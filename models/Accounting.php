<?php

namespace Models;

use Database\Database;

class Accounting
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getRevenueAndExpensesByYear($year)
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
    revenue_and_expenses AS (
        SELECT 
            m.month,
            COALESCE(s.total_sales, 0) AS revenue,
            COALESCE(p.total_purchase, 0) + COALESCE(e.total_expenses, 0) AS expenses
        FROM months m
        LEFT JOIN sales_inflow s ON m.month = s.month
        LEFT JOIN purchase_outflow p ON m.month = p.month
        LEFT JOIN expenses_outflow e ON m.month = e.month
    )
    SELECT 
        re.*,
        CASE 
            WHEN re.month = EXTRACT(MONTH FROM CURRENT_DATE) THEN TRUE
            ELSE FALSE
        END AS current_month
    FROM revenue_and_expenses re
    ORDER BY re.month;
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
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    private function calculateMeta($data)
    {
        $total_revenue = 0.0;
        $total_expenses = 0.0;

        $lowest_revenue = PHP_FLOAT_MAX;
        $highest_revenue = PHP_FLOAT_MIN;
        $lowest_expenses = PHP_FLOAT_MAX;
        $highest_expenses = PHP_FLOAT_MIN;

        foreach ($data as $row) {
            $revenue = (float) $row['revenue'];
            $expenses = (float) $row['expenses'];

            $total_revenue += $revenue;
            $total_expenses += $expenses;

            $lowest_revenue = min($lowest_revenue, $revenue);
            $highest_revenue = max($highest_revenue, $revenue);
            $lowest_expenses = min($lowest_expenses, $expenses);
            $highest_expenses = max($highest_expenses, $expenses);
        }

        return [
            'total_revenue' => round($total_revenue, 2),
            'total_expenses' => round($total_expenses, 2),
            'lowest_revenue' => $lowest_revenue === PHP_FLOAT_MAX ? 0.0 : round($lowest_revenue, 2),
            'highest_revenue' => $highest_revenue === PHP_FLOAT_MIN ? 0.0 : round($highest_revenue, 2),
            'lowest_expenses' => $lowest_expenses === PHP_FLOAT_MAX ? 0.0 : round($lowest_expenses, 2),
            'highest_expenses' => $highest_expenses === PHP_FLOAT_MIN ? 0.0 : round($highest_expenses, 2)
        ];
    }


    public function getAccountingOverview()
    {
        $query = "
            SELECT 
                (
                    SELECT COALESCE(SUM(total), 0)
                    FROM sales_orders
                ) AS total_revenue,
                (
                    SELECT COALESCE(SUM(total), 0)
                    FROM sales_orders
                ) - COALESCE((
                    SELECT SUM(total)
                    FROM purchase_orders
                ), 0) - COALESCE((
                    SELECT SUM(amount)
                    FROM expenses
                ), 0) AS cash_at_hand,
                (
                    COALESCE((
                        SELECT SUM(total)
                        FROM purchase_orders
                    ), 0) + COALESCE((
                        SELECT SUM(amount)
                        FROM expenses
                    ), 0)
                ) AS outgoing,
                (
                    SELECT COALESCE(SUM(amount), 0)
                    FROM loans
                    WHERE status != 'repaid'
                ) AS current_loaned
            ;
        ";

        $stmt = $this->db->query($query);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function insertExpense($data)
    {
        $sql = "INSERT INTO expenses (
                expense_title, expense_category, payment_method_id, payment_term_id,
                department_id, amount, bank_charges, date_of_expense, notes, status,
                processed_by
            ) VALUES (
                :expense_title, :expense_category, :payment_method_id, :payment_term_id,
                :department_id, :amount, :bank_charges, :date_of_expense, :notes, :status,
                :processed_by
            )";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':expense_title', $data['expense_title'] ?? null);
            $stmt->bindValue(':expense_category', $data['expense_category'] ?? null);
            $stmt->bindValue(':payment_method_id', $data['payment_method_id'] ?? null);
            $stmt->bindValue(':payment_term_id', $data['payment_term_id'] ?? null);
            $stmt->bindValue(':department_id', $data['department_id'] ?? null);
            $stmt->bindValue(':amount', $data['amount'] ?? null);
            $stmt->bindValue(':bank_charges', $data['bank_charges'] ?? null);
            $stmt->bindValue(':date_of_expense', $data['date_of_expense'] ?? null);
            $stmt->bindValue(':notes', $data['notes'] ?? null);
            $stmt->bindValue(':status', $data['status'] ?? null);
            $stmt->bindValue(':processed_by', $data['processed_by'] ?? null);

            $stmt->execute();
        } catch (\PDOException $e) {
            error_log('Insert expense failed: ' . $e->getMessage());
            throw new \Exception('Failed to insert expense.');
        }
    }

    public function getExpenses($filter = [])
    {
        $page = $filter['page'] ?? 1;
        $pageSize = $filter['page_size'] ?? 10;
        $offset = ($page - 1) * $pageSize;

        $conditions = [];
        $params = [];

        if (!empty($filter['start_date']) && !empty($filter['end_date'])) {
            $conditions[] = "e.date_of_expense BETWEEN :start_date AND :end_date";
            $params['start_date'] = $filter['start_date'];
            $params['end_date'] = $filter['end_date'];
        } elseif (!empty($filter['start_date'])) {
            $conditions[] = "e.date_of_expense >= :start_date";
            $params['start_date'] = $filter['start_date'];
        } elseif (!empty($filter['end_date'])) {
            $conditions[] = "e.date_of_expense <= :end_date";
            $params['end_date'] = $filter['end_date'];
        }

        if (!empty($filter['search'])) {
            $search = '%' . $filter['search'] . '%';
            $conditions[] = "(
                e.expense_title ILIKE :search 
                OR ec.name ILIKE :search 
                OR d.name ILIKE :search
                OR pm.name ILIKE :search
            )";
            $params['search'] = $search;
        }

        $totalItems = $this->countExpenses($conditions, $params);

        $sql = "SELECT 
                e.expense_id,
                e.expense_title,
                e.date_of_expense, 
                pm.name AS payment_method,
                ec.name AS expense_category,
                d.name AS department,
                e.amount
            FROM 
                expenses e
            LEFT JOIN 
                payment_methods pm ON e.payment_method_id = pm.id
            LEFT JOIN 
                expenses_categories ec ON e.expense_category = ec.id
            LEFT JOIN 
                departments d ON e.department_id = d.id";

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " LIMIT :pageSize OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':pageSize', $pageSize, \PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);

            foreach ($params as $key => $value) {
                $stmt->bindParam(":$key", $value);
            }

            $stmt->execute();
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $meta = [
                'total_data' => (int) $totalItems,
                'total_pages' => ceil($totalItems / $pageSize),
                'page_size' => (int) $pageSize,
                'previous_page' => $page > 1 ? (int) $page - 1 : null,
                'current_page' => (int) $page,
                'next_page' => $page < ceil($totalItems / $pageSize)
                    ? (int) $page + 1 : null,
            ];

            return ['data' => $data, 'meta' => $meta];

        } catch (\PDOException $e) {
            error_log('Fetch expenses failed: ' . $e->getMessage());
            throw new \Exception('Failed to fetch expenses.');
        }
    }

    private function countExpenses($conditions, $params)
    {
        $countSql = "SELECT COUNT(*) 
                 FROM expenses e
                 LEFT JOIN payment_methods pm ON e.payment_method_id = pm.id
                 LEFT JOIN expenses_categories ec ON e.expense_category = ec.id
                 LEFT JOIN departments d ON e.department_id = d.id";

        if (!empty($conditions)) {
            $countSql .= " WHERE " . implode(" AND ", $conditions);
        }

        try {
            $countStmt = $this->db->prepare($countSql);

            foreach ($params as $key => $value) {
                $countStmt->bindParam(":$key", $value);
            }

            $countStmt->execute();
            return $countStmt->fetchColumn();

        } catch (\PDOException $e) {
            error_log('Count expenses failed: ' . $e->getMessage());
            throw new \Exception('Failed to count expenses.');
        }
    }

    public function getBills($filter = [])
    {
        $page = $filter['page'] ?? 1;
        $pageSize = $filter['page_size'] ?? 10;
        $offset = ($page - 1) * $pageSize;

        $conditions = [];
        $params = [];

        // Validate and add status filter
        if (!empty($filter['status'])) {
            $conditions[] = "po.status = :status";
            $params['status'] = $filter['status'];
        }

        // Validate and add date filters
        if (!empty($filter['start_date']) && strtotime($filter['start_date'])) {
            $conditions[] = "po.date_received >= :start_date";
            $params['start_date'] = $filter['start_date'];
        }
        if (!empty($filter['end_date']) && strtotime($filter['end_date'])) {
            $conditions[] = "po.date_received <= :end_date";
            $params['end_date'] = $filter['end_date'];
        }

        // Add search filter
        if (!empty($filter['search'])) {
            $search = '%' . $filter['search'] . '%';
            $conditions[] = "(
            po.reference_number ILIKE :search 
            OR po.purchase_order_number ILIKE :search 
            OR v.display_name ILIKE :search
        )";
            $params['search'] = $search;
        }

        $totalItems = $this->countBills($conditions, $params);

        // Base query
        $sql = "SELECT 
            po.reference_number AS ref_id,
            po.purchase_order_number AS po_number,
            po.created_at AS date, 
            po.date_received + INTERVAL '30 days' AS due_date,
            v.display_name AS vendor_name, 
            po.total AS amount, 
            CASE 
                WHEN po.status = 'overdue' THEN 
                    CASE 
                        WHEN CURRENT_DATE - po.date_received = 1 THEN 
                            'overdue by 1 day'
                        WHEN CURRENT_DATE - po.date_received = 0 THEN 
                            'due today' 
                        ELSE 
                            'overdue by ' || (CURRENT_DATE - po.date_received) || ' days' 
                    END
                WHEN po.status = 'received' THEN 
                    'received'
            END AS status
        FROM 
            purchase_orders po
        LEFT JOIN 
            vendors v ON po.vendor_id = v.id
        WHERE 
            po.status IN ('received', 'overdue')";

        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        $sql .= " LIMIT :pageSize OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(':pageSize', $pageSize, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

            $stmt->execute();
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $meta = [
                'total_data' => (int) $totalItems,
                'total_pages' => ceil($totalItems / $pageSize),
                'page_size' => (int) $pageSize,
                'previous_page' => $page > 1 ? (int) $page - 1 : null,
                'current_page' => (int) $page,
                'next_page' => $page < ceil($totalItems / $pageSize)
                    ? (int) $page + 1 : null,
            ];

            return ['data' => $data, 'meta' => $meta];

        } catch (\PDOException $e) {
            error_log('Fetch purchase order bills failed: ' . $e->getMessage());
            throw new \Exception('Failed to fetch purchase order bills.');
        }
    }

    private function countBills($conditions, $params)
    {
        $countSql = "SELECT COUNT(*) 
                 FROM purchase_orders po
                 LEFT JOIN vendors v ON po.vendor_id = v.id
                 WHERE po.status IN ('received', 'overdue')";

        if (!empty($conditions)) {
            $countSql .= " AND " . implode(" AND ", $conditions);
        }

        try {
            $countStmt = $this->db->prepare($countSql);

            foreach ($params as $key => $value) {
                $countStmt->bindValue(":$key", $value);
            }

            $countStmt->execute();
            return $countStmt->fetchColumn();

        } catch (\PDOException $e) {
            error_log('Count purchase orders failed: ' . $e->getMessage());
            throw new \Exception('Failed to count purchase orders.');
        }
    }

    public function getSalesOrder($orderId)
    {
        $query = "
            SELECT
                so.id,
                so.order_id,
                so.order_title,
                so.invoice_number,
                pr.name AS recorded_by,
                so.order_type AS order_category,
                so.delivery_option,
                dl.name AS assigned_driver,
                COALESCE(SUM(soi.quantity), 0) AS quantity,
                CONCAT_WS(' ', c.salutation, c.first_name, c.last_name) AS customer_name,
                c.work_phone AS customer_work_phone,
                c.mobile_phone AS customer_mobile_phone,
                so.created_at::DATE AS date,
                so.total AS amount,
                so.status,
                (
                    SELECT JSON_AGG(
                        JSON_BUILD_OBJECT(
                            'name', pl.item_details,
                            'quantity', soi.quantity,
                            'price', soi.price,
                            'discount', so.discount,
                            'total', soi.total
                        )
                    )
                    FROM sales_order_items soi
                    LEFT JOIN price_lists pl ON soi.item_id = pl.id
                    WHERE soi.sales_order_id = so.id
                ) AS product_order
            FROM sales_orders so
            LEFT JOIN sales_order_items soi ON so.id = soi.sales_order_id
            LEFT JOIN customers c ON so.customer_id = c.id
            LEFT JOIN users pr ON so.processed_by = pr.id
            LEFT JOIN users dl ON so.assigned_driver_id = dl.id
            WHERE so.id = :order_id
            GROUP BY 
                so.id, so.order_id, so.order_title, so.invoice_number, pr.name,
                so.order_type, so.delivery_option, dl.name, c.salutation, 
                c.first_name, c.last_name, c.work_phone, c.mobile_phone,
                so.created_at, so.total, so.status
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue('order_id', $orderId);

        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result && isset($result['product_order'])) {
            $result['product_order'] = json_decode($result['product_order'], true);
        }

        return $result;
    }

    public function confirmSalesOrderPayment($orderId)
    {
        $query = "
            UPDATE sales_orders
            SET status = 'paid'
            WHERE id = :order_id
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue('order_id', $orderId);

        $stmt->execute();
    }

}
