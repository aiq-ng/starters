<?php

namespace Models;

use Database\Database;

class Sale
{
    private $db;

    public function __construct()
    {
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
            WHERE so.status = 'paid'
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

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':period', $period, \PDO::PARAM_STR);

        try {
            $stmt->execute();
            $revenueData = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            $revenueData = [];
        }

        $revenueDataWithDiffs = $this->addPreviousRevenueAndDiff($revenueData);

        $minMaxRevenue = $this->calculateMinMaxRevenue($revenueDataWithDiffs);

        return [
            'data' => $revenueDataWithDiffs,
            'meta' => $minMaxRevenue
        ];
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


    public function getSalesOverview($filter = ['when' => 'yesterday'])
    {
        $dateRanges = [
            'yesterday' => 'CURRENT_DATE - INTERVAL \'1 day\'',
            'lastweek' => 'CURRENT_DATE - INTERVAL \'7 days\'',
            'lastmonth' => 'CURRENT_DATE - INTERVAL \'1 month\'',
            'lastyear' => 'CURRENT_DATE - INTERVAL \'1 year\'',
        ];

        $when = $filter['when'] ?? 'yesterday';
        error_log($when);

        if (!isset($dateRanges[$when])) {
            throw new \InvalidArgumentException('Invalid filter provided.');
        }

        $comparisonDate = $dateRanges[$when];

        $query = "
        WITH current_data AS (
            SELECT
                COALESCE(SUM(so.total), 0) AS total_revenue,
                COUNT(DISTINCT so.id) AS total_orders,
                COUNT(DISTINCT soi.id) AS products_sold,
                COUNT(DISTINCT so.customer_id) AS total_customers
            FROM sales_orders so
            LEFT JOIN sales_order_items soi ON so.id = soi.sales_order_id
            WHERE so.created_at >= CURRENT_DATE
        ),
        previous_data AS (
            SELECT
                COALESCE(SUM(so.total), 0) AS total_revenue,
                COUNT(DISTINCT so.id) AS total_orders,
                COALESCE(SUM(soi.quantity), 0) AS products_sold,
                COUNT(DISTINCT so.customer_id) AS total_customers
            FROM sales_orders so
            LEFT JOIN sales_order_items soi ON so.id = soi.sales_order_id
            WHERE so.created_at BETWEEN $comparisonDate AND CURRENT_DATE - INTERVAL '1 day'
        )
        SELECT
            cd.total_revenue,
            pd.total_revenue AS previous_total_revenue,
            cd.total_orders,
            pd.total_orders AS previous_total_orders,
            cd.products_sold,
            pd.products_sold AS previous_products_sold,
            cd.total_customers,
            pd.total_customers AS previous_total_customers
        FROM current_data cd, previous_data pd
    ";

        $stmt = $this->db->query($query);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total_revenue' => (float) $result['total_revenue'],
            'total_revenue_percentage' => $this->calculatePercentageIncrease(
                $result['total_revenue'],
                $result['previous_total_revenue']
            ),
            'total_orders' => (int) $result['total_orders'],
            'total_orders_percentage' => $this->calculatePercentageIncrease(
                $result['total_orders'],
                $result['previous_total_orders']
            ),
            'products_sold' => (int) $result['products_sold'],
            'products_sold_percentage' => $this->calculatePercentageIncrease(
                $result['products_sold'],
                $result['previous_products_sold']
            ),
            'total_customers' => (int) $result['total_customers'],
            'total_customers_percentage' => $this->calculatePercentageIncrease(
                $result['total_customers'],
                $result['previous_total_customers']
            ),
        ];
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
        AND status = 'paid'
    ";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':start_date', $$period === 'today' ? $startOfDay : ($period === 'week' ? $startOfWeek : $startOfMonth));
        $stmt->bindParam(':end_date', $$period === 'today' ? $endOfDay : ($period === 'week' ? $endOfWeek : $endOfMonth));

        try {
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['total_sales'] ?? 0;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return 0;
        }
    }


    private function calculatePercentageIncrease($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }


    public function createPriceList($data)
    {
        $query = "
            INSERT INTO price_lists 
            (item_category_id, item_details, unit_price, minimum_order, unit_id)
            VALUES 
        ";

        $values = [];
        $placeholders = [];

        foreach ($data as $item) {
            $placeholders[] = "(?, ?, ?, ?, ?)";
            $values[] = $item['item_category_id'] ?? null;
            $values[] = $item['item_details'] ?? null;
            $values[] = $item['unit_price'] ?? null;
            $values[] = $item['minimum_order'] ?? null;
            $values[] = $item['unit_id'] ?? null;
        }

        $query .= implode(", ", $placeholders);

        $stmt = $this->db->prepare($query);

        $result = $stmt->execute($values);

        if ($result) {
            return $stmt->rowCount();
        }

        return null;
    }

    public function getPriceList($filters = [])
    {
        $page = $filters['page'] ?? 1;
        $pageSize = $filters['page_size'] ?? 10;

        $query = "
            SELECT 
                pl.id, 
                ic.name AS item_category, 
                pl.item_details, 
                pl.unit_price, 
                pl.minimum_order, 
                u.abbreviation AS unit
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
    }

    public function getPriceListCount($filters = [])
    {
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
    }


    public function getAPriceList($id)
    {
        $query = "
            SELECT 
                pl.id, 
                pl.item_category_id, 
                pl.item_details, 
                pl.unit_price, 
                pl.minimum_order, 
                pl.unit_id
            FROM 
                price_lists pl
            WHERE 
                pl.id = :id
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $data;

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
    }

    public function deletePriceList($id)
    {
        $query = "DELETE FROM price_lists WHERE id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount();
    }

    public function getSalesOrders($filters = [])
    {
        $page = $filters['page'] ?? 1;
        $pageSize = $filters['page_size'] ?? 10;
        $query = "
            SELECT
                so.id, 
                so.order_id, 
                so.order_title, 
                COALESCE(SUM(soi.quantity), 0) AS quantity, 
                CONCAT_WS(' ', c.salutation, c.first_name, c.last_name) AS customer_name, 
                so.created_at::DATE AS date, 
                so.order_type, 
                so.total AS amount, 
                so.status
            FROM 
                sales_orders so
            LEFT JOIN 
                sales_order_items soi 
                ON so.id = soi.sales_order_id
            LEFT JOIN 
                customers c 
                ON so.customer_id = c.id
        ";

        $conditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            $conditions[] = "so.status = :status";
            $params['status'] = $filters['status'];
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

        if ($conditions) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $query .= "
            GROUP BY 
                so.order_id, so.order_title, c.salutation, c.first_name, 
                c.last_name, so.created_at, so.order_type, so.total, so.status, so.id
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

        return ['data' => $data, 'meta' => $meta];
    }

    public function getServiceOrders($filters = [])
    {
        $page = $filters['page'] ?? 1;
        $pageSize = $filters['page_size'] ?? 10;

        $query = "
            SELECT
                so.order_title AS title,
                so.additional_note AS description,
                so.created_at,
                so.delivery_date
            FROM 
                sales_orders so
            WHERE 
                so.order_type = 'service'
            ORDER BY 
                so.delivery_date ASC
            LIMIT :limit OFFSET :offset
        ";

        $params = [
            'limit' => $pageSize,
            'offset' => ($page - 1) * $pageSize,
        ];

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }

        $stmt->execute();

        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $totalItems = $this->getServiceOrdersCount();

        $meta = [
            'total_data' => (int) $totalItems,
            'total_pages' => ceil($totalItems / $pageSize),
            'page_size' => (int) $pageSize,
            'previous_page' => $page > 1 ? (int) $page - 1 : null,
            'current_page' => (int) $page,
            'next_page' => (int) $page + 1,
        ];

        return ['data' => $data, 'meta' => $meta];
    }

    private function getServiceOrdersCount()
    {
        $query = "
        SELECT COUNT(*) AS total
        FROM sales_orders
        WHERE order_type = 'service'
    ";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function createSale($data)
    {

        $this->db->beginTransaction();

        try {
            $orderId = $this->insertSalesOrder($data);

            $this->insertSalesOrderItem($orderId, $data['items']);

            $this->db->commit();

            return $orderId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function getSalesOrdersCount($filters = [])
    {
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

        if (!empty($filters['status'])) {
            $conditions[] = "so.status = :status";
            $params['status'] = $filters['status'];
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

        if ($conditions) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }


    private function insertSalesOrder($data)
    {
        $query = "
            INSERT INTO sales_orders (
                order_type, order_title, payment_term_id, customer_id,
                payment_method_id, delivery_option, 
                assigned_driver_id, delivery_date, additional_note,
                customer_note, discount, delivery_charge, total
            ) 
            VALUES (
                :order_type, :order_title, :payment_term_id, :customer_id,
                :payment_method_id, :delivery_option, 
                :assigned_driver_id, :delivery_date, :additional_note,
                :customer_note, :discount, :delivery_charge,
                :total 
            ) 
            RETURNING id;
        ";

        try {
            $stmt = $this->db->prepare($query);

            $stmt->execute([
                ':order_type' => $data['order_type'] ?? null,
                ':order_title' => $data['order_title'] ?? null,
                ':payment_term_id' => $data['payment_term_id'] ?? null,
                ':customer_id' => $data['customer_id'] ?? null,
                ':payment_method_id' => $data['payment_method_id'] ?? null,
                ':delivery_option' => $data['delivery_option'] ?? null,
                ':assigned_driver_id' => $data['assigned_driver_id'] ?? null,
                ':delivery_date' => $data['delivery_date'] ?? null,
                ':additional_note' => $data['additional_note'] ?? null,
                ':customer_note' => $data['customer_note'] ?? null,
                ':discount' => $data['discount'] ?? null,
                ':delivery_charge' => $data['delivery_charge'] ?? null,
                ':total' => $data['total'] ?? null
            ]);

            return $stmt->fetchColumn();
        } catch (\PDOException $e) {
            throw new \Exception("Failed to insert sales order: " . $e->getMessage());
        }
    }

    private function insertSalesOrderItem($salesOrderId, $items)
    {
        $query = "
            INSERT INTO sales_order_items (sales_order_id, item_id, quantity, price) 
            VALUES (:sales_order_id, :item_id, :quantity, :price);
        ";

        $stmt = $this->db->prepare($query);

        foreach ($items as $item) {
            $item = array_filter($item, function ($value) {
                return $value !== "" && $value !== null;
            });

            if (!empty($item['item_id']) && !empty($item['quantity']) && !empty($item['price'])) {
                $stmt->execute([
                    ':sales_order_id' => $salesOrderId,
                    ':item_id' => $item['item_id'],
                    ':quantity' => $item['quantity'],
                    ':price' => $item['price']
                ]);
            }
        }
    }

    public function getInvoiceDetails($salesOrderId)
    {
        $query = "
            SELECT so.id,
                so.invoice_number,
                so.reference_number,
                so.order_title,
                so.order_type,
                c.display_name AS customer_name,
                c.email AS customer_email,
                so.discount,
                so.delivery_charge,
                so.total,
                so.created_at AS invoice_date,
                so.delivery_date,
                json_agg(
                    json_build_object(
                    'item_id', soi.item_id,
                    'item_name', i.name,
                    'item_description', i.description,
                        'quantity', soi.quantity,
                        'price', soi.price,
                        'ammount', soi.quantity * soi.price
                    )
                ) AS items
            FROM sales_orders so
            LEFT JOIN customers c ON so.customer_id = c.id
            LEFT JOIN sales_order_items soi ON soi.sales_order_id = so.id
            LEFT JOIN items i ON soi.item_id = i.id
            WHERE so.id = :sales_order_id
            GROUP BY so.id, c.display_name, so.invoice_number, so.order_title, so.order_type, 
                     so.discount, so.delivery_charge, so.total, c.email, so.created_at, so.delivery_date;
        ";

        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute([':sales_order_id' => $salesOrderId]);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                $result['items'] = json_decode($result['items'], true);
                return $result;
            }

        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch invoice details: " . $e->getMessage());
        }
    }

    public function getTopSellingStock($filter)
    {
        $page = isset($filter['page']) && is_numeric($filter['page']) ? (int)$filter['page'] : 1;
        $pageSize = isset($filter['page_size']) && is_numeric($filter['page_size']) ? (int)$filter['page_size'] : 10;
        $offset = ($page - 1) * $pageSize;

        $query = "
            SELECT 
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
    }
}
