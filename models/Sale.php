<?php

namespace Models;

use Database\Database;
use Services\NotificationService;
use Controllers\BaseController;

class Sale extends Kitchen
{
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }

    public function getRevenue($period = 'week')
    {
        $query = "
        WITH period_series AS (
            SELECT
                ps.period
            FROM
                (SELECT CASE
                    WHEN :period = 'today' THEN 1
                    WHEN :period = 'week' THEN 2
                    WHEN :period = 'month' THEN 3
                    WHEN :period = 'year' THEN 4
                END AS period) AS p
            CROSS JOIN LATERAL (
                SELECT generate_series(
                    CASE
                        WHEN :period = 'today' THEN 0
                        WHEN :period = 'week' THEN 1
                        WHEN :period = 'month' THEN 1
                        WHEN :period = 'year' THEN 1
                    END,
                    CASE
                        WHEN :period = 'today' THEN 23
                        WHEN :period = 'week' THEN 7
                        WHEN :period = 'month' THEN 4
                        WHEN :period = 'year' THEN 12
                    END
                )
            ) AS ps(period)
        ),
        revenue_data AS (
            SELECT
                CASE
                    WHEN :period = 'today' THEN EXTRACT(HOUR FROM so.created_at)
                    WHEN :period = 'week' THEN EXTRACT(DOW FROM so.created_at)
                    WHEN :period = 'month' THEN EXTRACT(WEEK FROM so.created_at) -
                    EXTRACT(WEEK FROM DATE_TRUNC('month', CURRENT_DATE)) + 1
                    WHEN :period = 'year' THEN EXTRACT(MONTH FROM so.created_at)
                END AS period,
                SUM(so.total) AS revenue
            FROM sales_orders so
            WHERE so.payment_status = 'paid'
            AND so.created_at >= 
                CASE
                    WHEN :period = 'today' THEN CURRENT_DATE
                    WHEN :period = 'week' THEN DATE_TRUNC('week', CURRENT_DATE)
                    WHEN :period = 'month' THEN DATE_TRUNC('month', CURRENT_DATE)
                    WHEN :period = 'year' THEN DATE_TRUNC('year', CURRENT_DATE)
                END
            AND so.created_at < 
                CASE
                    WHEN :period = 'today' THEN CURRENT_DATE + INTERVAL '1 day'
                    WHEN :period = 'week' THEN DATE_TRUNC('week', CURRENT_DATE) + INTERVAL '1 week'
                    WHEN :period = 'month' THEN DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '1 month'
                    WHEN :period = 'year' THEN DATE_TRUNC('year', CURRENT_DATE) + INTERVAL '1 year'
                END
            GROUP BY period
        )
        SELECT
            ps.period,
            COALESCE(rd.revenue, 0) AS revenue,
            CASE
                WHEN ps.period = CASE
                    WHEN :period = 'today' THEN EXTRACT(HOUR FROM CURRENT_TIMESTAMP)
                    WHEN :period = 'week' THEN EXTRACT(DOW FROM CURRENT_TIMESTAMP)
                    WHEN :period = 'month' THEN EXTRACT(WEEK FROM CURRENT_TIMESTAMP) -
                    EXTRACT(WEEK FROM DATE_TRUNC('month', CURRENT_DATE)) + 1
                    WHEN :period = 'year' THEN EXTRACT(MONTH FROM CURRENT_TIMESTAMP)
                END THEN TRUE
                ELSE FALSE
            END AS current_period
        FROM period_series ps
        LEFT JOIN revenue_data rd ON ps.period = rd.period
        ORDER BY ps.period;
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':period', $period, \PDO::PARAM_STR);

            try {
                $stmt->execute();
                $revenueData = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                error_log($e->getMessage());
                $revenueData = [];
            }

            $revenueDataWithDiffs = $this->addPreviousRevenueAndDiff($revenueData);

            $minMaxRevenue = $this->calculateMinMaxRevenue($revenueDataWithDiffs);

            return [
                'data' => $revenueDataWithDiffs,
                'meta' => $minMaxRevenue
            ];
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return ['data' => [], 'meta' => []];
        }
    }

    public function addPreviousRevenueAndDiff($revenueData)
    {
        $previousRevenue = null;
        $result = [];

        foreach ($revenueData as $data) {
            $currentRevenue = $data['revenue'];

            $percentageDiff = null;
            if ($previousRevenue !== null && $previousRevenue != 0) {
                $percentageDiff = ($currentRevenue - $previousRevenue) / $previousRevenue * 100;
            } else {
                $percentageDiff = 0;
            }

            $result[] = [
                'period' => $data['period'],
                'revenue' => $currentRevenue,
                'previous_revenue' => $previousRevenue ?? 0,
                'percentage_diff' => (string) round($percentageDiff, 2),
                'current' => $data['current_period']
            ];

            $previousRevenue = $currentRevenue;
        }

        return $result;
    }
    public function calculateMinMaxRevenue($revenueData)
    {
        $minRevenue = INF;
        $maxRevenue = -INF;

        foreach ($revenueData as $data) {
            $revenue = $data['revenue'];
            if ($revenue < $minRevenue) {
                $minRevenue = $revenue;
            }
            if ($revenue > $maxRevenue) {
                $maxRevenue = $revenue;
            }
        }
        return [
            'min_revenue' => $minRevenue === INF ? 0 : $minRevenue,
            'max_revenue' => $maxRevenue === -INF ? 0 : $maxRevenue
        ];
    }


    public function getSalesOverview($filter = ['when' => 'today'])
    {
        try {
            $dateRanges = [
                'today' => 'CURRENT_DATE',
                'yesterday' => 'CURRENT_DATE - INTERVAL \'1 day\'',
                'lastweek' => 'CURRENT_DATE - INTERVAL \'7 days\'',
                'lastmonth' => 'CURRENT_DATE - INTERVAL \'1 month\'',
                'lastyear' => 'CURRENT_DATE - INTERVAL \'1 year\'',
            ];

            $when = $filter['when'] ?? 'today';
            error_log($when);

            if (!isset($dateRanges[$when])) {
                throw new \InvalidArgumentException('Invalid filter provided.');
            }

            $startDate = $dateRanges[$when];

            $query = "
                SELECT
                    COALESCE(SUM(DISTINCT so.total), 0) AS total_revenue,
                    COUNT(DISTINCT so.id) AS total_orders,
                    COUNT(DISTINCT soi.id) AS products_sold,
                    COUNT(DISTINCT so.customer_id) AS total_customers
                FROM sales_orders so
                LEFT JOIN sales_order_items soi ON so.id = soi.sales_order_id
                WHERE DATE_TRUNC('day', so.created_at) >= $startDate AND payment_status = 'paid'
            ";

            $stmt = $this->db->query($query);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return [
                'total_revenue' => (float) $result['total_revenue'],
                'total_orders' => (int) $result['total_orders'],
                'products_sold' => (int) $result['products_sold'],
                'total_customers' => (int) $result['total_customers'],
            ];
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return ['total_revenue' => 0, 'total_orders' => 0, 'products_sold' => 0, 'total_customers' => 0];
        }
    }

    public function getTotalSales($period = 'today')
    {
        $now = new \DateTime();

        switch ($period) {
            case 'today':
                $startOfDay = $now->setTime(0, 0, 0)->format('Y-m-d H:i:s');
                $endOfDay = $now->setTime(23, 59, 59)->format('Y-m-d H:i:s');
                break;
            case 'week':
                $startOfWeek = $now->modify('this week')->setTime(0, 0, 0)->format('Y-m-d H:i:s');
                $endOfWeek = $now->modify('this week +6 days')->setTime(23, 59, 59)->format('Y-m-d H:i:s');
                break;
            case 'month':
                $startOfMonth = $now->modify('first day of this month')->setTime(0, 0, 0)->format('Y-m-d H:i:s');
                $endOfMonth = $now->modify('last day of this month')->setTime(23, 59, 59)->format('Y-m-d H:i:s');
                break;
            default:
                throw new \InvalidArgumentException("Invalid period: $period");
        }

        $query = "
            SELECT SUM(total) AS total_sales
            FROM sales_orders
            WHERE created_at BETWEEN :start_date AND :end_date
            AND payment_status = 'paid'
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':start_date', $$period === 'today' ? $startOfDay : ($period === 'week' ? $startOfWeek : $startOfMonth));
        $stmt->bindParam(':end_date', $$period === 'today' ? $endOfDay : ($period === 'week' ? $endOfWeek : $endOfMonth));

        try {
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['total_sales'] ?? 0;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return 0;
        }
    }


    public function createPriceList($data)
    {
        $query = "
            INSERT INTO price_lists 
            (item_category_id, item_details, unit_price, minimum_order, unit_id, tax_id, description)
            VALUES 
        ";

        $values = [];
        $placeholders = [];

        try {
            foreach ($data as $item) {
                $placeholders[] = "(?, ?, ?, ?, ?, ?, ?)";
                $values[] = !empty($item['item_category_id']) ? $item['item_category_id'] : null;
                $values[] = !empty($item['item_details']) ? $item['item_details'] : null;
                $values[] = !empty($item['unit_price']) ? $item['unit_price'] : null;
                $values[] = !empty($item['minimum_order']) ? $item['minimum_order'] : null;
                $values[] = !empty($item['unit_id']) ? $item['unit_id'] : null;
                $values[] = !empty($item['tax_id']) ? $item['tax_id'] : null;
                $values[] = !empty($item['description']) ? $item['description'] : null;
            }

            $query .= implode(", ", $placeholders);

            $stmt = $this->db->prepare($query);

            $result = $stmt->execute($values);

            if ($result) {
                return $stmt->rowCount();
            }

            return null;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Failed to create price list. " . $e->getMessage());
        }
    }

    public function getPriceList($filters = [])
    {
        try {
            $page = $filters['page'] ?? 1;
            $pageSize = $filters['page_size'] ?? 10;

            $query = "
                SELECT 
                    pl.id,
                    pl.order_sequence AS serial_number,
                    ic.name AS item_category, 
                    pl.item_details, 
                    pl.unit_price, 
                    pl.minimum_order,
                    pl.description,
                    u.abbreviation AS unit,
                    pl.tax_id
                FROM 
                    price_lists pl
                LEFT JOIN 
                    item_categories ic 
                    ON pl.item_category_id = ic.id
                LEFT JOIN 
                    units u 
                    ON pl.unit_id = u.id
            ";

            $conditions = [];
            $params = [];

            if (!empty($filters['item_category'])) {
                $conditions[] = "ic.name = :item_category";
                $params['item_category'] = $filters['item_category'];
            }

            if (!empty($filters['min_price'])) {
                $conditions[] = "pl.unit_price >= :min_price";
                $params['min_price'] = $filters['min_price'];
            }

            if (!empty($filters['max_price'])) {
                $conditions[] = "pl.unit_price <= :max_price";
                $params['max_price'] = $filters['max_price'];
            }

            if (!empty($filters['search'])) {
                $conditions[] = "(pl.item_details ILIKE :search OR ic.name ILIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            if ($conditions) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }

            $query .= "
                ORDER BY 
                    pl.created_at DESC 
                LIMIT :limit OFFSET :offset
            ";

            $params['limit'] = $pageSize;
            $params['offset'] = ($page - 1) * $pageSize;

            $stmt = $this->db->prepare($query);

            foreach ($params as $key => $value) {
                $type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $type);
            }

            $stmt->execute();

            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $totalItems = $this->getPriceListCount($filters);

            $meta = [
                'total_data' => (int) $totalItems,
                'total_pages' => ceil($totalItems / $pageSize),
                'page_size' => (int) $pageSize,
                'previous_page' => $page > 1 ? (int) $page - 1 : null,
                'current_page' => (int) $page,
                'next_page' => (int) $page + 1,
            ];

            return ['data' => $data, 'meta' => $meta];
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return ['data' => [], 'meta' => []];
        }
    }

    public function getPriceListCount($filters = [])
    {
        try {
            $query = "
                SELECT 
                    COUNT(*) AS total
                FROM 
                    price_lists pl
                LEFT JOIN 
                    item_categories ic 
                    ON pl.item_category_id = ic.id
            ";

            $conditions = [];
            $params = [];

            if (!empty($filters['item_category'])) {
                $conditions[] = "ic.name = :item_category";
                $params['item_category'] = $filters['item_category'];
            }

            if (!empty($filters['min_price'])) {
                $conditions[] = "pl.unit_price >= :min_price";
                $params['min_price'] = $filters['min_price'];
            }

            if (!empty($filters['max_price'])) {
                $conditions[] = "pl.unit_price <= :max_price";
                $params['max_price'] = $filters['max_price'];
            }

            if (!empty($filters['search'])) {
                $conditions[] = "(pl.item_details ILIKE :search OR ic.name ILIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            if ($conditions) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }

            $stmt = $this->db->prepare($query);

            foreach ($params as $key => $value) {
                $type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $type);
            }

            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return 0;
        }
    }


    public function getAPriceList($id)
    {
        try {
            $query = "
                SELECT 
                    pl.id,
                    pl.order_sequence AS serial_number,
                    pl.item_category_id, 
                    pl.item_details, 
                    pl.unit_price, 
                    pl.minimum_order, 
                    pl.unit_id,
                    pl.description,
                    pl.tax_id
                FROM 
                    price_lists pl
                WHERE 
                    pl.id = :id
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $id]);

            $data = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $data;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return [];
        }

    }

    public function updatePriceList($data)
    {
        $query = "
            UPDATE price_lists 
            SET 
                item_category_id = :item_category_id, 
                item_details = :item_details, 
                unit_price = :unit_price, 
                minimum_order = :minimum_order, 
                unit_id = :unit_id
                WHERE 
                id = :id
        ";

        try {
            $stmt = $this->db->prepare($query);

            $result = $stmt->execute([
                'item_category_id' => $data['item_category_id'] ?? null,
                'item_details' => $data['item_details'] ?? null,
                'unit_price' => $data['unit_price'] ?? null,
                'minimum_order' => $data['minimum_order'] ?? null,
                'unit_id' => $data['unit_id'] ?? null,
                'id' => $data['id'] ?? null
            ]);

            if ($result) {
                return $stmt->rowCount();
            }

            return false;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Failed to update price list");
        }
    }

    public function deletePriceList($ids)
    {
        if (empty($ids)) {
            return false;
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            $query = "DELETE FROM price_lists WHERE id IN ($placeholders)";

            $stmt = $this->db->prepare($query);
            $stmt->execute($ids);

            return $stmt->rowCount();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Failed to delete price list");
        }
    }

    public function getSalesOrders($filters = [])
    {
        try {
            $page = $filters['page'] ?? 1;
            $pageSize = $filters['page_size'] ?? 10;
            $query = "
                SELECT
                    so.id, 
                    so.order_id, 
                    so.order_title, 
                    COALESCE(SUM(soi.quantity), 0) AS quantity,
                    COALESCE(c.display_name, so.customer) AS customer_name, 
                    so.created_at::DATE AS date, 
                    so.order_type, 
                    so.total AS amount,
                    ROUND(SUM(soi.total * t.rate) / 100, 2) AS tax_amount,
                    so.status,
                    so.payment_status,
                    so.created_at AS created_datetime,
                    so.delivery_date,
                    so.delivery_time
                FROM 
                    sales_orders so
                LEFT JOIN 
                    sales_order_items soi 
                    ON so.id = soi.sales_order_id
                LEFT JOIN 
                    customers c 
                    ON so.customer_id = c.id
                LEFT JOIN taxes t
                    ON soi.tax_id = t.id
            ";

            $conditions = [];
            $params = [];

            if (!empty($filters['status']) && strtolower($filters['status']) !== 'all') {
                $conditions[] = "so.status = :status";
                $params['status'] = $filters['status'];
            }

            if (!empty($filters['payment_status']) && strtolower($filters['payment_status']) !== 'all') {
                $conditions[] = "so.payment_status = :payment_status";
                $params['payment_status'] = $filters['payment_status'];
            }

            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $conditions[] = "so.created_at::DATE BETWEEN :start_date AND :end_date";
                $params['start_date'] = $filters['start_date'];
                $params['end_date'] = $filters['end_date'];
            } elseif (!empty($filters['start_date'])) {
                $conditions[] = "so.created_at::DATE >= :start_date";
                $params['start_date'] = $filters['start_date'];
            } elseif (!empty($filters['end_date'])) {
                $conditions[] = "so.created_at::DATE <= :end_date";
                $params['end_date'] = $filters['end_date'];
            }

            if (!empty($filters['order_type'])) {
                $conditions[] = "so.order_type = :order_type";
                $params['order_type'] = $filters['order_type'];
            }

            if (!empty($filters['time'])) {
                $conditions[] = "so.delivery_time = :time";
                $params['time'] = $filters['time'];
            }

            if (!empty($filters['search'])) {
                $conditions[] = "
                (
                    so.order_title ILIKE :search OR 
                    so.order_id::TEXT ILIKE :search OR 
                    CONCAT_WS(' ', c.salutation, c.first_name, c.last_name) ILIKE :search
                )
            ";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            if ($conditions) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }

            $query .= "
                GROUP BY 
                    so.order_id, so.order_title, c.display_name, 
                    so.created_at, so.order_type, so.total, so.status,
                    so.id, so.total, so.created_at, so.delivery_date, so.delivery_time
                ORDER BY 
                    so.created_at DESC 
                LIMIT :limit OFFSET :offset
            ";

            $params['limit'] = $pageSize;
            $params['offset'] = ($page - 1) * $pageSize;

            $stmt = $this->db->prepare($query);

            foreach ($params as $key => $value) {
                $type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $type);
            }

            $stmt->execute();

            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $totalItems = $this->getSalesOrdersCount($filters);

            $meta = [
                'total_data' => (int) $totalItems,
                'total_pages' => ceil($totalItems / $pageSize),
                'page_size' => (int) $pageSize,
                'previous_page' => $page > 1 ? (int) $page - 1 : null,
                'current_page' => (int) $page,
                'next_page' => (int) $page + 1,
            ];

            $meta = array_merge($meta, $this->getOrderCount());

            return ['data' => $data, 'meta' => $meta];
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return ['data' => [], 'meta' => []];
        }
    }

    private function getSalesOrdersCount($filters = [])
    {
        try {
            $query = "
                SELECT COUNT(DISTINCT so.order_id) AS count
                FROM sales_orders so
                LEFT JOIN sales_order_items soi 
                ON so.id = soi.sales_order_id
                LEFT JOIN customers c 
                ON so.customer_id = c.id
            ";

            $conditions = [];
            $params = [];

            if (!empty($filters['status']) && strtolower($filters['status']) !== 'all') {
                $conditions[] = "so.status = :status";
                $params[':status'] = $filters['status'];
            }

            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $conditions[] = "so.created_at::DATE BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $filters['start_date'];
                $params[':end_date'] = $filters['end_date'];
            } elseif (!empty($filters['start_date'])) {
                $conditions[] = "so.created_at::DATE >= :start_date";
                $params[':start_date'] = $filters['start_date'];
            } elseif (!empty($filters['end_date'])) {
                $conditions[] = "so.created_at::DATE <= :end_date";
                $params[':end_date'] = $filters['end_date'];
            }

            if (!empty($filters['order_type'])) {
                $conditions[] = "so.order_type = :order_type";
                $params[':order_type'] = $filters['order_type'];
            }

            if (!empty($filters['search'])) {
                $conditions[] = "
                (
                    so.order_title ILIKE :search OR 
                    so.order_id::TEXT ILIKE :search OR 
                    CONCAT_WS(' ', c.salutation, c.first_name, c.last_name) ILIKE :search
                )
            ";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            if ($conditions) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }

            $stmt = $this->db->prepare($query);

            foreach ($params as $key => $value) {
                $type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $type);
            }

            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    public function getServiceOrders($filters = [])
    {
        try {
            $page = $filters['page'] ?? 1;
            $pageSize = $filters['page_size'] ?? 10;

            $query = "
                SELECT
                    SO.id,
                    so.order_title AS title,
                    so.additional_note AS description,
                    so.created_at,
                    so.delivery_date
                FROM 
                    sales_orders so
                WHERE 
                    so.order_type = 'service'
            ";

            $conditions = [];
            $params = [
                'limit' => $pageSize,
                'offset' => ($page - 1) * $pageSize,
            ];

            if (!empty($filters['search'])) {
                $conditions[] = "(so.order_title ILIKE :search OR so.additional_note ILIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            if ($conditions) {
                $query .= " AND " . implode(' AND ', $conditions);
            }

            $query .= "
                ORDER BY 
                    so.delivery_date ASC
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $this->db->prepare($query);

            foreach ($params as $key => $value) {
                $type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $type);
            }

            $stmt->execute();

            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $totalItems = $this->getServiceOrdersCount($filters);

            $meta = [
                'total_data' => (int) $totalItems,
                'total_pages' => ceil($totalItems / $pageSize),
                'page_size' => (int) $pageSize,
                'previous_page' => $page > 1 ? (int) $page - 1 : null,
                'current_page' => (int) $page,
                'next_page' => (int) $page + 1,
            ];

            return ['data' => $data, 'meta' => $meta];
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return ['data' => [], 'meta' => []];
        }
    }

    private function getServiceOrdersCount($filters = [])
    {
        try {
            $query = "
                SELECT COUNT(*) AS total
                FROM sales_orders so
                WHERE so.order_type = 'service'
            ";

            $conditions = [];
            $params = [];

            if (!empty($filters['search'])) {
                $conditions[] = "(so.order_title ILIKE :search OR so.additional_note ILIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            if ($conditions) {
                $query .= " AND " . implode(' AND ', $conditions);
            }

            $stmt = $this->db->prepare($query);

            foreach ($params as $key => $value) {
                $type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $type);
            }

            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    public function createSale($data)
    {

        $this->db->beginTransaction();

        try {
            $orderId = $this->insertSalesOrder($data);

            $this->insertSalesOrderItem($orderId, $data['items']);

            $this->db->commit();

            return $this->getInvoiceDetails($orderId);
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('Failed to create sale: ' . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    private function insertSalesOrder($data)
    {
        error_log('Insert Data: ' . json_encode($data));
        $query = "
            INSERT INTO sales_orders (
                order_type, order_title, payment_term_id, customer_id,
                payment_method_id, delivery_option, 
                delivery_date, delivery_time, delivery_address,
                additional_note, customer_note, discount_id, delivery_charge, total, processed_by,
                delivery_charge_id, status
            ) 
            VALUES (
                :order_type, :order_title, :payment_term_id, :customer_id,
                :payment_method_id, :delivery_option, 
                :delivery_date, :delivery_time, :delivery_address, 
                :additional_note, :customer_note, :discount_id, :delivery_charge,
                :total, :processed_by, :delivery_charge_id, :status
            ) 
            RETURNING id;
        ";

        try {

            $stmt = $this->db->prepare($query);

            $stmt->execute([
                ':order_type' => $data['order_type'] ?? 'order',
                ':order_title' => $data['order_title'] ?? null,
                ':payment_term_id' => $data['payment_term_id'] ?? null,
                ':customer_id' => $data['customer_id'] ?? null,
                ':payment_method_id' => $data['payment_method_id'] ?? null,
                ':delivery_option' => $data['delivery_option'] ?? null,
                ':delivery_date' => $data['delivery_date'] ?? null,
                ':delivery_time' => $data['delivery_time'] ?? null,
                ':delivery_address' => $data['delivery_address'] ?? null,
                ':additional_note' => $data['additional_note'] ?? null,
                ':customer_note' => $data['customer_note'] ?? null,
                ':discount_id' => $data['discount_id'] ?? null,
                ':delivery_charge' => $data['delivery_charge'] ?? null,
                ':total' => $data['total'] ?? null,
                ':processed_by' => $data['user_id'] ?? null,
                ':delivery_charge_id' => $data['delivery_charge_id'] ?? null,
                ':status' => $data['status'] ?? 'pending'
            ]);

            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            throw new \Exception("Failed to insert sales order: " . $e->getMessage());
        }
    }

    private function insertSalesOrderItem($salesOrderId, $items)
    {
        $query = "
        INSERT INTO sales_order_items 
        (sales_order_id, item_id, quantity, price, tax_id, platter_items) 
        VALUES (:sales_order_id, :item_id, :quantity, :price, :tax_id, :platter_items);
        ";

        $stmt = $this->db->prepare($query);
        try {
            foreach ($items as $item) {
                $item = array_filter($item, function ($value) {
                    return $value !== "" && $value !== null;
                });

                if (!empty($item['item_id']) && !empty($item['quantity'])) {
                    $stmt->execute([
                        ':sales_order_id' => $salesOrderId,
                        ':item_id' => $item['item_id'],
                        ':quantity' => $item['quantity'],
                        ':price' => isset($item['price']) && $item['price'] > 0
                            ? $item['price']
                            : $this->getPrice($item['item_id']),
                        ':tax_id' => isset($item['tax_id']) && $item['tax_id'] > 0
                            ? $item['tax_id']
                            : null,
                        ':platter_items' => $item['platter_items'] ?? null
                    ]);
                }
            }
        } catch (\Exception $e) {
            error_log("Error inserting sales order items: " . $e->getMessage());
            throw new \Exception("Failed to insert sales order items " . $e->getMessage());
        }
    }

    public function duplicateSale($saleId, $modifications = [])
    {
        $this->db->beginTransaction();

        try {
            $originalSale = $this->getSaleById($saleId);
            $originalItems = $this->getSaleItemsById($saleId);

            if (!$originalSale) {
                throw new \Exception("Sale not found");
            }

            $newSaleData = array_merge($originalSale, $modifications);
            unset($newSaleData['id']);

            $newSaleId = $this->insertSalesOrder($newSaleData);

            foreach ($originalItems as $item) {
                $item['sales_order_id'] = $newSaleId;
                unset($item['id']);
                $this->insertSalesOrderItem($newSaleId, [$item]);
            }

            $this->db->commit();

            return $this->getInvoiceDetails($newSaleId);
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Failed to duplicate sale: " . $e->getMessage());
            throw new \Exception("Failed to duplicate sale");
        }
    }

    private function getSaleById($saleId)
    {
        $query = "SELECT * FROM sales_orders WHERE id = :saleId";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':saleId' => $saleId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function getSaleItemsById($saleId)
    {
        $query = "SELECT * FROM sales_order_items WHERE sales_order_id = :saleId";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':saleId' => $saleId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    private function getPrice($itemId)
    {
        $query = "SELECT unit_price FROM price_lists WHERE id = :itemId";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute(['itemId' => $itemId]);

            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("Error fetching price: " . $e->getMessage());
            return 0;
        }

    }

    public function updateSale($id, $data)
    {
        $this->db->beginTransaction();

        try {
            $this->updateSalesOrder($id, $data);

            if (!empty($data['items'])) {
                $this->updateSalesOrderItems($id, $data['items']);
            }

            $this->db->commit();

            return $this->getInvoiceDetails($id);
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('Failed to update sale: ' . $e->getMessage());
            throw new \Exception('Failed to update sale');
        }
    }

    private function updateSalesOrder($id, $data)
    {
        unset($data['items']);

        $filteredData = array_filter($data, function ($value) {
            return $value !== "" && $value !== null;
        });

        $setClauses = [];
        $params = [':id' => $id];

        foreach ($filteredData as $field => $value) {
            $setClauses[] = "$field = :$field";
            $params[":$field"] = $value;
        }

        $setClauseString = implode(', ', $setClauses);

        $query = "
            UPDATE sales_orders
            SET $setClauseString
            WHERE id = :id
            RETURNING id;
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            throw new \Exception("Failed to update sales order: " . $e->getMessage());
        }
    }

    private function updateSalesOrderItems($salesOrderId, $items)
    {
        try {
            $existingItemIds = $this->getSalesOrderItems($salesOrderId);
            $incomingItemIds = array_column($items, 'item_id');
            $itemsToDelete = array_diff($existingItemIds, $incomingItemIds);

            foreach ($itemsToDelete as $itemToDelete) {
                $this->deleteSalesOrderItem($salesOrderId, $itemToDelete);
            }

            foreach ($items as $item) {
                $item = array_filter($item, fn ($value) => $value !== "" && $value !== null);

                if (empty($item['item_id'])) {
                    continue;
                }

                if (in_array($item['item_id'], $existingItemIds)) {
                    $query = "
                    UPDATE sales_order_items
                    SET 
                        quantity = COALESCE(:quantity, quantity),
                        price = COALESCE(:price, price),
                        tax_id = COALESCE(:tax_id, tax_id)
                    WHERE sales_order_id = :sales_order_id 
                    AND item_id = :item_id
                ";
                } else {
                    $query = "
                    INSERT INTO sales_order_items (sales_order_id, item_id, quantity, price, tax_id)
                    VALUES (:sales_order_id, :item_id, :quantity, :price, :tax_id)
                ";
                }

                $stmt = $this->db->prepare($query);
                $stmt->execute([
                    ':sales_order_id' => $salesOrderId,
                    ':item_id' => $item['item_id'],
                    ':quantity' => $item['quantity'] ?? null,
                    ':price' => $item['price'] ?? null,
                    ':tax_id' => $item['tax_id'] ?? null
                ]);
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Failed to update sales order items: " . $e->getMessage());
        }
    }

    private function getSalesOrderItems($salesOrderId)
    {
        $query = "
            SELECT 
                item_id
            FROM 
                sales_order_items
            WHERE 
                sales_order_id = :sales_order_id
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute(['sales_order_id' => $salesOrderId]);

            return $stmt->fetchAll(\PDO::FETCH_COLUMN);

        } catch (\Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    private function deleteSalesOrderItem($salesOrderId, $itemId)
    {
        $query = "
            DELETE FROM sales_order_items
            WHERE sales_order_id = :sales_order_id
            AND item_id = :item_id
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute(['sales_order_id' => $salesOrderId, 'item_id' => $itemId]);

            return $stmt->rowCount();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Failed to delete sales order item");
        }
    }

    public function deleteSalesOrder($ids)
    {
        if (empty($ids)) {
            return false;
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            $query = "DELETE FROM sales_orders WHERE id IN ($placeholders)";

            $stmt = $this->db->prepare($query);
            $stmt->execute($ids);

            return $stmt->rowCount();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Failed to delete sales order");
        }
    }

    public function patchSalesOrder($orderId, $data)
    {
        $setClauses = [];
        $params = [':order_id' => $orderId];

        foreach ($data as $field => $value) {
            $setClauses[] = "$field = :$field";
            $params[":$field"] = $value;
        }

        $setClauseString = implode(', ', $setClauses);
        $query = "
            UPDATE sales_orders
            SET $setClauseString
            WHERE id = :order_id
            RETURNING id;
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            throw new \Exception("Failed to update sales order: " . $e->getMessage());
        }
    }


    public function getInvoiceDetails($salesOrderId)
    {
        $query = "
            SELECT so.*,
                TO_CHAR(delivery_time, 'HH12:MI AM') AS delivery_time,
                dc.discount_type,
                c.id AS customer_id,
                COALESCE(c.display_name, so.customer) AS customer_name,
                c.address AS customer_address,
                c.mobile_phone AS customer_phone,
                ROUND(
                    SUM((soi.price * soi.quantity) * COALESCE(t.rate, soi.tax) / 100), 
                    2
                ) AS tax_amount,
                c.email AS customer_email,
                c.balance,
                u.name AS sales_rep_name,
                so.created_at::DATE AS invoice_date,
                (
                    SELECT json_agg(items_data ORDER BY items_data.created_at ASC)
                    FROM (
                        SELECT 
                            soi.item_id,
                            COALESCE(p.item_details, soi.item_name) AS item_name,    
                            p.item_details AS item_name,
                            soi.quantity,
                            soi.price,
                            soi.quantity * soi.price AS amount,
                            soi.total,
                            soi.tax_id,
                            COALESCE(t.rate, soi.tax) AS tax_rate,
                            ROUND((soi.price * soi.quantity) * COALESCE(t.rate, soi.tax) / 100, 2)  AS tax_amount,
                            soi.created_at
                        FROM sales_order_items soi
                        LEFT JOIN price_lists p ON soi.item_id = p.id
                        LEFT JOIN taxes t ON soi.tax_id = t.id
                        WHERE soi.sales_order_id = so.id
                        ORDER BY soi.created_at ASC
                    ) AS items_data
                ) AS items
            FROM sales_orders so
            LEFT JOIN sales_order_items soi ON so.id = soi.sales_order_id
            LEFT JOIN customers c ON so.customer_id = c.id
            LEFT JOIN users u ON so.processed_by = u.id
            LEFT JOIN discounts dc ON so.discount_id = dc.id
            LEFT JOIN delivery_charges dch ON so.delivery_charge_id = dch.id
            LEFT JOIN taxes t ON soi.tax_id = t.id
            WHERE so.id = :sales_order_id
            GROUP BY so.id, c.display_name, so.invoice_number, so.order_title,
                    so.order_type, c.id, so.payment_term_id, so.payment_method_id,
                    so.delivery_option, so.additional_note,
                    so.customer_note, so.discount, so.delivery_charge, so.total,
                    c.email, so.created_at, so.delivery_date, so.delivery_charge_id,
                    c.first_name, c.last_name, u.name, dch.amount,
                    dc.value, dc.discount_type
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([':sales_order_id' => $salesOrderId]);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                $result['items'] = !empty($result['items'])
                    ? json_decode($result['items'], true)
                    : [];
                return $result;
            }

        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Failed to fetch invoice details: " . $e->getMessage());
        }
    }

    public function getTopSellingStock($filter)
    {
        try {
            $page = isset($filter['page']) && is_numeric($filter['page'])
                ? (int)$filter['page'] : 1;
            $pageSize = isset($filter['page_size']) && is_numeric($filter['page_size'])
                ? (int)$filter['page_size'] : 10;
            $offset = ($page - 1) * $pageSize;

            $query = "
                SELECT
                    pl.id, 
                    pl.item_details AS name,
                    SUM(soi.quantity) AS total_quantity,
                    pl.unit_price AS price,
                    (SUM(soi.quantity) * pl.unit_price) AS total_amount
                FROM sales_order_items soi
                JOIN price_lists pl ON soi.item_id = pl.id
                GROUP BY pl.id, pl.item_details, pl.unit_price
                ORDER BY total_quantity DESC
                LIMIT ? OFFSET ?
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$pageSize, $offset]);
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $countQuery = "
                SELECT COUNT(DISTINCT pl.id) AS total
                FROM sales_order_items soi
                JOIN price_lists pl ON soi.item_id = pl.id
            ";

            $countStmt = $this->db->prepare($countQuery);
            $countStmt->execute();
            $totalItems = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            return [
                'data' => $data,
                'meta' => [
                    'total_data' => (int) $totalItems,
                    'total_pages' => ceil($totalItems / $pageSize),
                    'page_size' => (int) $pageSize,
                    'previous_page' => $page > 1 ? (int) $page - 1 : null,
                    'current_page' => (int) $page,
                    'next_page' => (int) $page + 1,

                ],
            ];
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return ['data' => [], 'meta' => []];
        }
    }

    public function sendToKitchen($orderIds)
    {
        try {

            if (!is_array($orderIds)) {
                $orderIds = [$orderIds];
            }

            $this->db->beginTransaction();

            $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
            $query = "
                UPDATE sales_orders
                SET status = 'new order'
                WHERE id IN ($placeholders) AND status = 'pending'
            ";

            $stmt = $this->db->prepare($query);
            foreach ($orderIds as $index => $orderId) {
                $stmt->bindValue($index + 1, $orderId);
            }
            $stmt->execute();

            $filters = [
                'status' => 'new order',
            ];

            $sales = $this->getNewOrders($filters);
            $usersToNotify = BaseController::getUserByRole('Admin');

            if (empty($usersToNotify)) {
                throw new \Exception("Admin user not found for notification.");
            }

            foreach ($usersToNotify as $userToNotify) {
                if (!isset($userToNotify['id'])) {
                    continue;
                }

                $notification = [
                    'user_id' => $userToNotify['id'],
                    'event' => 'update',
                    'title' => 'New Sales Orders',
                    'body' => 'New sales orders have been placed',
                    'event_data' => $sales['data'],
                ];

                (new NotificationService())->sendNotification($notification, false);
            }

            $this->updateSentToKitchen($orderIds);

            $this->db->commit();

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error in sendToKitchen: " . $e->getMessage());
            throw new \Exception("An error occurred while confirming sales order payments.");
        }
    }

    private function updateSentToKitchen($orderIds)
    {
        try {

            if (!is_array($orderIds)) {
                $orderIds = [$orderIds];
            }

            $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
            $query = "UPDATE sales_orders SET sent_to_kitchen = TRUE WHERE id IN ($placeholders)";

            $stmt = $this->db->prepare($query);
            foreach ($orderIds as $index => $orderId) {
                $stmt->bindValue($index + 1, $orderId);
            }
            $stmt->execute();

        } catch (\Exception $e) {
            error_log("Database error in updateSentToKitchen: " . $e->getMessage());
            throw new \Exception("An error occurred while updating sent_to_kitchen status.");
        }
    }

    public function voidSalesOrder($orderId)
    {
        try {
            $query = "
                UPDATE sales_orders
                SET status = 'void'
                WHERE id = :order_id
                RETURNING id
            ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':order_id', $orderId);
            $stmt->execute();

            return $stmt->fetchColumn();

        } catch (\Exception $e) {
            error_log("Error in voidSalesOrder: " . $e->getMessage());
            throw new \Exception("An error occurred while voiding sales order.");
        }
    }

}
