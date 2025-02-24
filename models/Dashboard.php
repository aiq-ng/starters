<?php

namespace Models;

use Database\Database;

class Dashboard
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getOverview()
    {
        $query = "
        SELECT 
            (SELECT COUNT(*) FROM item_categories) AS categories,
            (SELECT COUNT(*) FROM items) AS total_products,
            (SELECT COUNT(*) FROM vendors) AS vendors,
            (SELECT COUNT(*) FROM items WHERE availability = 'low stock') AS low_stocks
        ";

        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return [
                'categories' => 0,
                'total_products' => 0,
                'vendors' => 0,
                'low_stocks' => 0,
            ];
        }
    }

    public function getCashFlowByYear($year)
    {
        $query = "
            WITH months AS (
                SELECT generate_series(1, 12) AS month
            ),
            sales_inflow AS (
                SELECT 
                    EXTRACT(MONTH FROM COALESCE(so.delivery_date, so.created_at)) AS month,
                    SUM(so.total) AS total_sales
                FROM sales_orders so
                WHERE EXTRACT(YEAR FROM COALESCE(so.delivery_date, so.created_at)) = :year
                AND so.payment_status IN ('paid')
                GROUP BY EXTRACT(MONTH FROM COALESCE(so.delivery_date, so.created_at))
            ),
            purchase_outflow AS (
                SELECT 
                    EXTRACT(MONTH FROM COALESCE(po.delivery_date, po.created_at)) AS month,
                    SUM(po.total) AS total_purchase
                FROM purchase_orders po
                WHERE EXTRACT(YEAR FROM COALESCE(po.delivery_date, po.created_at)) = :year
                AND po.status IN ('paid')
                GROUP BY EXTRACT(MONTH FROM COALESCE(po.delivery_date, po.created_at))
            ),
            expenses_outflow AS (
                SELECT 
                    EXTRACT(MONTH FROM COALESCE(e.date_of_expense, e.created_at)) AS month,
                    SUM(e.amount + COALESCE(e.bank_charges, 0)) AS total_expenses
                FROM expenses e
                WHERE EXTRACT(YEAR FROM COALESCE(e.date_of_expense, e.created_at)) = :year
                AND e.status IN ('paid', 'pending')
                GROUP BY EXTRACT(MONTH FROM COALESCE(e.date_of_expense, e.created_at))
            ),
            cash_flows AS (
                SELECT 
                    m.month,
                    COALESCE(s.total_sales, 0) AS total_sales,
                    COALESCE(p.total_purchase, 0) AS total_purchase,
                    COALESCE(e.total_expenses, 0) AS total_expenses,
                    (COALESCE(s.total_sales, 0) - 
                        (COALESCE(p.total_purchase, 0) + 
                        COALESCE(e.total_expenses, 0))) AS cash_flow
                FROM months m
                LEFT JOIN sales_inflow s ON m.month = s.month
                LEFT JOIN purchase_outflow p ON m.month = p.month
                LEFT JOIN expenses_outflow e ON m.month = e.month
            ),
            cash_flows_with_prev_month AS (
                SELECT
                    cf.*,
                    COALESCE(LAG(cf.cash_flow) OVER (ORDER BY cf.month), 0) AS prev_month_cash_flow
                FROM cash_flows cf
            )
            SELECT 
                cf.*,
                CASE 
                    WHEN cf.month = EXTRACT(MONTH FROM CURRENT_DATE) THEN TRUE
                    ELSE FALSE
                END AS current_month,
                CASE
                    WHEN cf.month > EXTRACT(MONTH FROM CURRENT_DATE) THEN 
                        (SELECT AVG(cash_flow) FROM cash_flows 
                            WHERE month <= EXTRACT(MONTH FROM CURRENT_DATE)) 
                    ELSE cf.cash_flow
                END AS estimated_cash_flow,
                ROUND(
                    CASE 
                        WHEN cf.prev_month_cash_flow = 0 THEN 0
                        ELSE ((cf.cash_flow - cf.prev_month_cash_flow) * 100.0) / cf.prev_month_cash_flow
                    END, 2) AS percentage_diff
            FROM cash_flows_with_prev_month cf
            ORDER BY cf.month;
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':year', $year, \PDO::PARAM_INT);

        try {
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $meta = $this->calculateMeta($result);

            return [
                'data' => $result,
                'meta' => $meta
            ];
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return ['data' => [], 'meta' => []];
        }
    }

    private function calculateMeta($data)
    {
        $total_sales = 0;
        $total_purchase = 0;
        $total_expenses = 0;
        $total_cash_flow = 0;
        $prev_month_cash_flow = null;

        $percentage_increases = [];

        foreach ($data as $row) {
            $total_sales += $row['total_sales'];
            $total_purchase += $row['total_purchase'];
            $total_expenses += $row['total_expenses'];
            $total_cash_flow += $row['cash_flow'];

            if ($prev_month_cash_flow !== null) {
                $percentage_increase = $prev_month_cash_flow == 0
                    ? 0
                    : (($row['cash_flow'] - $prev_month_cash_flow) * 100) / $prev_month_cash_flow;
                $percentage_increases[] = $percentage_increase;
            }

            $prev_month_cash_flow = $row['cash_flow'];
        }

        $least_percentage_increase = $percentage_increases ? min($percentage_increases) : 0;
        $highest_percentage_increase = $percentage_increases ? max($percentage_increases) : 0;

        return [
            'total_sales' => $total_sales,
            'total_purchase' => $total_purchase,
            'total_expenses' => $total_expenses,
            'total_cash_flow' => $total_cash_flow,
            'least_percentage_increase' => $least_percentage_increase,
            'highest_percentage_increase' => $highest_percentage_increase
        ];
    }

    public function getBusinessOverview($filters = [])
    {
        $year = $filters['year'] ?? date('Y');

        try {

            $query = "
                SELECT 
                    COALESCE((SELECT SUM(total) 
                              FROM sales_orders 
                              WHERE payment_status = 'paid' 
                              AND DATE_PART('year', COALESCE(delivery_date, created_at)) = :year), 0) AS total_income,
                    COALESCE((SELECT SUM(total) 
                              FROM purchase_orders 
                              WHERE status = 'paid' 
                              AND DATE_PART('year', COALESCE(delivery_date, created_at)) = :year), 0) 
                    +
                    COALESCE((SELECT SUM(amount) 
                              FROM expenses 
                              WHERE status IN ('paid', 'pending') 
                              AND DATE_PART('year', COALESCE(date_of_expense, created_at)) = :year), 0) AS total_expenses,
                    COALESCE((SELECT COUNT(*) 
                              FROM vendors), 0) AS total_vendors,
                    COALESCE((SELECT COUNT(*) 
                              FROM users), 0) AS total_employees
            ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':year', (int) $year, \PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [
                'total_income' => 0,
                'total_expenses' => 0,
                'total_vendors' => 0,
                'total_employees' => 0
            ];
        }
    }

    public function getLowQuantityStock($filters = [])
    {
        $page = $filters['page'] ?? 1;
        $pageSize = $filters['page_size'] ?? 5;
        $offset = ($page - 1) * $pageSize;

        $query = "
            SELECT 
                i.id, 
                i.name, 
                i.media,
                CONCAT(
                    COALESCE(SUM(item_stocks.quantity), 0), ' ', u.abbreviation
                ) AS remaining_quantity,
                i.availability
            FROM items i
            LEFT JOIN item_stocks ON i.id = item_stocks.item_id
            LEFT JOIN units u ON i.unit_id = u.id
            WHERE i.availability = 'low stock'
            GROUP BY 
                i.id, i.name, i.media, u.abbreviation
            LIMIT :pageSize OFFSET :offset
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue('pageSize', $pageSize, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);

        try {
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as &$row) {
                $row['media'] = $row['media'] ? json_decode($row['media'], true) : null;
            }

            $totalItems = $this->getLowStockCount();

            $meta = [
                'total_data' => (int) $totalItems,
                'total_pages' => ceil($totalItems / $pageSize),
                'page_size' => (int) $pageSize,
                'previous_page' => $page > 1 ? (int) $page - 1 : null,
                'current_page' => (int) $page,
                'next_page' => $page < ceil($totalItems / $pageSize) ? (int) $page + 1 : null,
            ];

            return [
                'data' => $result,
                'meta' => $meta,
            ];
        } catch (\Exception $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [
                'data' => [],
                'meta' => [],
            ];
        }
    }

    private function getLowStockCount(): int
    {
        $query = "
            SELECT COUNT(DISTINCT i.id) AS total_items
            FROM items i
            LEFT JOIN item_stocks ON i.id = item_stocks.item_id
            WHERE i.availability = 'low stock'
        ";

        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log('Database Error: ' . $e->getMessage());
            return 0;
        }
    }

    public function getMostPurchasedProducts($filters = [])
    {
        try {
            $page = $filters['page'] ?? 1;
            $pageSize = $filters['page_size'] ?? 5;
            $offset = ($page - 1) * $pageSize;

            $query = "
                SELECT  
                    i.id AS item_id,
                    i.name AS item_name,
                    CONCAT(SUM(poi.quantity), '(', u.name, ')') AS purchased_quantity,
                    i.opening_stock - SUM(poi.quantity) AS remaining_quantity,
                    poi.price AS price,
                    SUM(poi.quantity * poi.price) AS amount
                FROM
                    purchase_order_items poi
                JOIN
                    items i ON poi.item_id = i.id
                JOIN
                    units u ON i.unit_id = u.id
            ";

            $conditions = [];
            $params = [];

            if (!empty($filters['month'])) {
                $conditions[] = "DATE_PART('month', poi.created_at) = :month";
                $params['month'] = (int) $filters['month'];
            }

            if (!empty($filters['year'])) {
                $conditions[] = "DATE_PART('year', poi.created_at) = :year";
                $params['year'] = (int) $filters['year'];
            }

            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }

            $query .= "
                GROUP BY i.id, i.name, i.opening_stock, u.name, poi.price, i.id
                ORDER BY purchased_quantity DESC
                LIMIT :pageSize OFFSET :offset
            ";

            $params['pageSize'] = $pageSize;
            $params['offset'] = $offset;

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
            }
            $stmt->execute();
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $totalItems = $this->getTotalItemCount('purchase_order_items', $filters);

            $meta = [
                'total_data' => (int) $totalItems,
                'total_pages' => ceil($totalItems / $pageSize),
                'page_size' => (int) $pageSize,
                'previous_page' => $page > 1 ? (int) $page - 1 : null,
                'current_page' => (int) $page,
                'next_page' => (int) $page + 1,
            ];

            return [
                'data' => $data,
                'meta' => $meta,
    ];
        } catch (\Exception $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [
                'data' => [],
                'meta' => [],
            ];
        }
    }

    public function getBestSellingProducts($filters = [])
    {
        try {
            $page = $filters['page'] ?? 1;
            $pageSize = $filters['page_size'] ?? 5;
            $offset = ($page - 1) * $pageSize;

            $query = "
                SELECT
                    pl.id AS item_id, 
                    pl.item_details AS item_name,
                    CONCAT(SUM(soi.quantity), ' ', u.abbreviation, '') AS sold_quantity,
                    pl.minimum_order AS remaining_quantity,
                    soi.price AS price,
                    SUM(soi.quantity * soi.price) AS amount
                FROM 
                    sales_order_items soi
                LEFT JOIN 
                    price_lists pl ON soi.item_id = pl.id
                LEFT JOIN 
                    units u ON pl.unit_id = u.id
            ";

            $conditions = [];
            $params = [];

            if (!empty($filters['month'])) {
                $conditions[] = "DATE_PART('month', soi.created_at) = :month";
                $params['month'] = (int) $filters['month'];
            }

            if (!empty($filters['year'])) {
                $conditions[] = "DATE_PART('year', soi.created_at) = :year";
                $params['year'] = (int) $filters['year'];
            }

            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }

            $query .= "
            GROUP BY 
                pl.item_details, pl.minimum_order, u.name, soi.price, u.abbreviation, pl.id
            ORDER BY 
                sold_quantity DESC
            LIMIT 
                :page_size OFFSET :offset
        ";

            $params['page_size'] = $pageSize;
            $params['offset'] = $offset;

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
            }

            $stmt->execute();
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $totalItems = $this->getBestSellingCount('sales_order_items', $filters);

            $meta = [
                'total_data' => $totalItems,
                'total_pages' => ceil($totalItems / $pageSize),
                'page_size' => $pageSize,
                'previous_page' => $page > 1 ? $page - 1 : null,
                'current_page' => $page,
                'next_page' => $page + 1 <= ceil($totalItems / $pageSize) ? $page + 1 : null,
            ];

            return [
                'data' => $data,
                'meta' => $meta,
    ];
        } catch (\Exception $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [
                'data' => [],
                'meta' => [],
            ];
        }
    }

    private function getBestSellingCount($table, $filters = [])
    {
        try {
            $countQuery = "
                SELECT COUNT(DISTINCT pl.id) AS total_items
                FROM $table soi
                JOIN price_lists pl ON soi.item_id = pl.id
            ";

            $conditions = [];
            $params = [];

            if (!empty($filters['month'])) {
                $conditions[] = "DATE_PART('month', soi.created_at) = :month";
                $params['month'] = (int) $filters['month'];
            }

            if (!empty($filters['year'])) {
                $conditions[] = "DATE_PART('year', soi.created_at) = :year";
                $params['year'] = (int) $filters['year'];
            }

            if (!empty($conditions)) {
                $countQuery .= " WHERE " . implode(" AND ", $conditions);
            }

            $stmt = $this->db->prepare($countQuery);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
            }

            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log('Database Error: ' . $e->getMessage());
            return 0;
        }
    }

    private function getTotalItemCount($table, $filters = [])
    {
        try {
            $countQuery = "
                SELECT COUNT(DISTINCT i.id) AS total_items
                FROM $table oi
                JOIN items i ON oi.item_id = i.id
            ";

            $conditions = [];
            $params = [];

            if (!empty($filters['month'])) {
                $conditions[] = "DATE_PART('month', oi.created_at) = :month";
                $params['month'] = (int) $filters['month'];
            }

            if (!empty($filters['year'])) {
                $conditions[] = "DATE_PART('year', oi.created_at) = :year";
                $params['year'] = (int) $filters['year'];
            }

            if (!empty($conditions)) {
                $countQuery .= " WHERE " . implode(" AND ", $conditions);
            }

            $stmt = $this->db->prepare($countQuery);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
            }
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log('Database Error: ' . $e->getMessage());
            return 0;
        }
    }
}
