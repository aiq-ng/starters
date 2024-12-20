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
        } catch (\PDOException $e) {
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
                EXTRACT(MONTH FROM so.delivery_date) AS month,
                SUM(so.total) AS total_sales
            FROM sales_orders so
            WHERE EXTRACT(YEAR FROM so.delivery_date) = :year
            AND so.status = 'paid'
            GROUP BY EXTRACT(MONTH FROM so.delivery_date)
        ),

        purchase_outflow AS (
            SELECT 
                EXTRACT(MONTH FROM po.delivery_date) AS month,
                SUM(po.total) AS total_purchase
            FROM purchase_orders po
            WHERE EXTRACT(YEAR FROM po.delivery_date) = :year
            AND po.status = 'paid'
            GROUP BY EXTRACT(MONTH FROM po.delivery_date)
        ),

        expenses_outflow AS (
            SELECT 
                EXTRACT(MONTH FROM e.date_of_expense) AS month,
                SUM(e.amount + COALESCE(e.bank_charges, 0)) AS total_expenses
            FROM expenses e
            WHERE EXTRACT(YEAR FROM e.date_of_expense) = :year
            AND e.status = 'paid'
            GROUP BY EXTRACT(MONTH FROM e.date_of_expense)
        ),

        cash_flows AS (
            SELECT 
                m.month,
                COALESCE(s.total_sales, 0) AS total_sales,
                COALESCE(p.total_purchase, 0) AS total_purchase,
                COALESCE(e.total_expenses, 0) AS total_expenses,
                (COALESCE(s.total_sales, 0) - (COALESCE(p.total_purchase, 0) + COALESCE(e.total_expenses, 0))) AS cash_flow,
                ROUND(
                    CASE 
                        WHEN COALESCE(s.total_sales, 0) + COALESCE(p.total_purchase, 0) + COALESCE(e.total_expenses, 0) = 0 THEN 0
                        ELSE ((COALESCE(s.total_sales, 0) - (COALESCE(p.total_purchase, 0) + COALESCE(e.total_expenses, 0))) * 100.0) / 
                            (COALESCE(s.total_sales, 0) + COALESCE(p.total_purchase, 0) + COALESCE(e.total_expenses, 0))
                    END, 2) AS percentage_diff
            FROM months m
            LEFT JOIN sales_inflow s ON m.month = s.month
            LEFT JOIN purchase_outflow p ON m.month = p.month
            LEFT JOIN expenses_outflow e ON m.month = e.month
        )
        SELECT 
            cf.*,
            CASE 
                WHEN cf.month = EXTRACT(MONTH FROM CURRENT_DATE) THEN TRUE
                ELSE FALSE
            END AS current_month,
            CASE
                WHEN cf.month > EXTRACT(MONTH FROM CURRENT_DATE) THEN 
                    -- Estimate future cash flows based on average cash flow from past months
                    (SELECT AVG(cash_flow) FROM cash_flows WHERE month <= EXTRACT(MONTH FROM CURRENT_DATE)) 
                ELSE cf.cash_flow
            END AS estimated_cash_flow
        FROM cash_flows cf
        ORDER BY cf.month;
        ";

        // Prepare and execute the query
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':year', $year, \PDO::PARAM_INT);

        try {
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getBusinessOverview($filters = [])
    {
        $month = $filters['month'] ?? date('n');
        $year = $filters['year'] ?? date('Y');

        $query = "
            SELECT 
                COALESCE((SELECT SUM(total) 
                          FROM sales_orders 
                          WHERE status IN ('completed') 
                          AND DATE_PART('month', created_at) = :month
                          AND DATE_PART('year', created_at) = :year), 0) AS total_income,
                COALESCE((SELECT SUM(total) 
                          FROM purchase_orders 
                          WHERE status IN ('paid') 
                          AND DATE_PART('month', created_at) = :month
                          AND DATE_PART('year', created_at) = :year), 0) AS total_expenses,
                COALESCE((SELECT COUNT(*) 
                          FROM vendors), 0) AS total_vendors,
                COALESCE((SELECT COUNT(*) 
                          FROM users), 0) AS total_employees
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':month', (int) $month, \PDO::PARAM_INT);
        $stmt->bindValue(':year', (int) $year, \PDO::PARAM_INT);

        try {
            $stmt->execute();
            $currentMonthResult = $stmt->fetch(\PDO::FETCH_ASSOC);

            $previousMonth = ($month == 1) ? 12 : $month - 1;
            $previousYear = ($month == 1) ? $year - 1 : $year;

            $queryPreviousMonth = "
                SELECT 
                    COALESCE((SELECT SUM(total) 
                              FROM sales_orders 
                              WHERE status IN ('sent', 'completed') 
                              AND DATE_PART('month', created_at) = :previousMonth
                              AND DATE_PART('year', created_at) = :previousYear), 0) AS total_income,
                    COALESCE((SELECT SUM(total) 
                              FROM purchase_orders 
                              WHERE status IN ('processing', 'completed') 
                              AND DATE_PART('month', created_at) = :previousMonth
                              AND DATE_PART('year', created_at) = :previousYear), 0) AS total_expenses
            ";

            $stmtPreviousMonth = $this->db->prepare($queryPreviousMonth);
            $stmtPreviousMonth->bindValue(':previousMonth', (int) $previousMonth, \PDO::PARAM_INT);
            $stmtPreviousMonth->bindValue(':previousYear', (int) $previousYear, \PDO::PARAM_INT);

            $stmtPreviousMonth->execute();
            $previousMonthResult = $stmtPreviousMonth->fetch(\PDO::FETCH_ASSOC);

            $percentageIncomeChange = $this->calculatePercentageChange(
                $currentMonthResult['total_income'],
                $previousMonthResult['total_income']
            );
            $percentageExpensesChange = $this->calculatePercentageChange(
                $currentMonthResult['total_expenses'],
                $previousMonthResult['total_expenses']
            );

            return [
                'total_income' => $currentMonthResult['total_income'],
                'total_expenses' => $currentMonthResult['total_expenses'],
                'percentage_income_change' => $percentageIncomeChange,
                'percentage_expenses_change' => $percentageExpensesChange,
                'total_vendors' => $currentMonthResult['total_vendors'],
                'total_employees' => $currentMonthResult['total_employees']
            ];

        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return [
                'total_income' => 0,
                'total_expenses' => 0,
                'percentage_income_change' => 0,
                'percentage_expenses_change' => 0,
                'total_vendors' => 0,
                'total_employees' => 0
            ];
        }
    }

    private function calculatePercentageChange($currentValue, $previousValue)
    {
        if ($previousValue == 0) {
            return $currentValue == 0 ? 0 : 100;
        }

        return (($currentValue - $previousValue) / $previousValue) * 100;
    }

    public function getLowQuantityStock()
    {
        $query = "
            SELECT 
                i.id, 
                i.name, 
                i.media,
            CONCAT(COALESCE(SUM(item_stocks.quantity), 0), ' ', u.abbreviation) AS remaining_quantity,
            i.availability
            FROM items i
            LEFT JOIN item_stocks ON i.id = item_stocks.item_id
            LEFT JOIN units u ON i.unit_id = u.id
            WHERE i.availability = 'low stock'
            GROUP BY 
                i.id, i.name, i.media, u.abbreviation
        ";

        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as &$row) {
                $row['media'] = $row['media'] ? json_decode($row['media'], true) : null;
            }

            return $result;
        } catch (\PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [];
        }
    }

    public function getMostPurchasedItems($filters = [])
    {
        $page = $filters['page'] ?? 1;
        $pageSize = $filters['page_size'] ?? 5;
        $offset = ($page - 1) * $pageSize;

        $query = "
            SELECT 
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
            GROUP BY i.id, i.name, i.opening_stock, u.name, poi.price
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
    }

    public function getBestSellingItems($filters = [])
    {
        $page = $filters['page'] ?? 1;
        $pageSize = $filters['page_size'] ?? 5;
        $offset = ($page - 1) * $pageSize;

        $query = "
            SELECT 
                i.name AS item_name,
                SUM(soi.quantity) AS sold_quantity,
                i.opening_stock - SUM(soi.quantity) AS remaining_quantity,
                soi.price AS price,
                SUM(soi.quantity * soi.price) AS amount
            FROM
                sales_order_items soi
            JOIN
                items i ON soi.item_id = i.id
            JOIN
                units u ON i.unit_id = u.id
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
            GROUP BY i.id, i.name, i.opening_stock, u.name, soi.price
            ORDER BY sold_quantity DESC
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

        $totalItems = $this->getTotalItemCount('sales_order_items', $filters);

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
    }

    private function getTotalItemCount($table, $filters = [])
    {
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
    }
}
