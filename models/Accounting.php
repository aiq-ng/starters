<?php

namespace Models;

use Database\Database;
use Services\NotificationService;
use Controllers\BaseController;

class Accounting extends Kitchen
{
    private $db;

    public function __construct()
    {
        parent::__construct();
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
                EXTRACT(MONTH FROM COALESCE(so.delivery_date, so.created_at)) AS month,
                SUM(so.total) AS total_sales
            FROM sales_orders so
            WHERE EXTRACT(YEAR FROM COALESCE(so.delivery_date, so.created_at)) = :year
            AND so.payment_status = 'paid'
            GROUP BY EXTRACT(MONTH FROM COALESCE(so.delivery_date, so.created_at))
        ),
        purchase_outflow AS (
            SELECT 
                EXTRACT(MONTH FROM COALESCE(po.delivery_date, po.created_at)) AS month,
                SUM(po.total) AS total_purchase
            FROM purchase_orders po
            WHERE EXTRACT(YEAR FROM COALESCE(po.delivery_date, po.created_at)) = :year
            AND po.status = 'paid'
            GROUP BY EXTRACT(MONTH FROM COALESCE(po.delivery_date, po.created_at))
        ),
        expenses_outflow AS (
            SELECT 
                EXTRACT(MONTH FROM COALESCE(e.date_of_expense, e.created_at)) AS month,
                SUM(e.amount + COALESCE(e.bank_charges, 0)) AS total_expenses
            FROM expenses e
            WHERE EXTRACT(YEAR FROM COALESCE(e.date_of_expense, e.created_at)) = :year
            AND e.status IN ('pending', 'paid')
            GROUP BY EXTRACT(MONTH FROM COALESCE(e.date_of_expense, e.created_at))
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
        } catch (\Exception $e) {
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


    public function getAccountingOverview($year)
    {

        $query = "
            SELECT 
                COALESCE((
                    SELECT SUM(total) 
                    FROM sales_orders 
                    WHERE payment_status = 'paid'
                    AND DATE_PART('year', COALESCE(delivery_date, created_at)) = :year
                ), 0) AS total_revenue,
                
                COALESCE((
                    SELECT MAX(total) 
                    FROM sales_orders 
                    WHERE payment_status = 'paid'
                    AND DATE_PART('year', COALESCE(delivery_date, created_at)) = :year
                ), 0) AS highest_revenue,

                COALESCE((
                    SELECT SUM(total) 
                    FROM purchase_orders 
                    WHERE status = 'paid'
                    AND DATE_PART('year', COALESCE(delivery_date, created_at)) = :year
                ), 0) 
                + COALESCE((
                    SELECT SUM(amount) 
                    FROM expenses 
                    WHERE status IN ('pending', 'paid')
                    AND DATE_PART('year', COALESCE(date_of_expense, created_at)) = :year
                ), 0) AS total_expenses,

                COALESCE(GREATEST(
                    (
                        SELECT MAX(total) 
                        FROM purchase_orders 
                        WHERE status = 'paid'
                        AND DATE_PART('year', COALESCE(delivery_date, created_at)) = :year
                    ),
                    (
                        SELECT MAX(amount) 
                        FROM expenses 
                        WHERE status IN ('pending', 'paid')
                        AND DATE_PART('year', COALESCE(date_of_expense, created_at)) = :year
                    )
                ), 0) AS highest_expenses
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute(['year' => $year]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return [];
        }
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
            $stmt->bindValue(':status', $data['status'] ?? 'paid');
            $stmt->bindValue(':processed_by', $data['processed_by'] ?? null);

            $stmt->execute();
        } catch (\Exception $e) {
            error_log('Insert expense failed: ' . $e->getMessage());
            throw new \Exception('Failed to insert expense.');
        }
    }

    public function deleteExpense($expenseIds)
    {
        if (empty($expenseIds)) {
            return 0;
        }

        error_log('Deleting expenses: ' . implode(',', $expenseIds));

        $placeholders = implode(',', array_fill(0, count($expenseIds), '?'));


        try {
            $stmt = $this->db->prepare("DELETE FROM expenses WHERE id IN ($placeholders)");
            $stmt->execute($expenseIds);

            return $stmt->rowCount();
        } catch (\Exception $e) {
            error_log('Delete expenses failed: ' . $e->getMessage());
            throw new \Exception('Failed to delete expenses.');
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
                e.id, 
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

        } catch (\Exception $e) {
            error_log('Fetch expenses failed: ' . $e->getMessage());
            return ['data' => [], 'meta' => []];
        }
    }

    public function getExpense($expenseId)
    {
        $query = "
            SELECT 
                e.*,
                pm.name AS payment_method,
                ec.name AS expense_category,
                d.name AS department,
                pt.name AS payment_term,
                u.firstname || ' ' || u.lastname AS processed_by
            FROM 
                expenses e
            LEFT JOIN 
                payment_methods pm ON e.payment_method_id = pm.id
            LEFT JOIN 
                payment_terms pt ON e.payment_term_id = pt.id
            LEFT JOIN
                users u ON e.processed_by = u.id
            LEFT JOIN
                expenses_categories ec ON e.expense_category = ec.id
            LEFT JOIN 
                departments d ON e.department_id = d.id
            WHERE 
                e.id = :expense_id
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':expense_id', $expenseId);

            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            error_log('Fetch expense failed: ' . $e->getMessage());
            return false;
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

        } catch (\Exception $e) {
            error_log('Count expenses failed: ' . $e->getMessage());
            return 0;
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
            po.id, 
            po.reference_number AS ref_id,
            po.purchase_order_number AS po_number,
            po.date_received AS date, 
            po.payment_due_date AS due_date,
            v.display_name AS vendor_name, 
            po.total AS amount, 
            CASE 
                WHEN po.status = 'overdue' THEN 
                    CASE 
                        WHEN CURRENT_DATE - po.payment_due_date = 1 THEN 
                            'overdue by 1 day'
                        WHEN CURRENT_DATE - po.payment_due_date = 0 THEN 
                            'due today' 
                        ELSE 
                            'overdue by ' || (CURRENT_DATE - po.payment_due_date) || ' days' 
                    END
                ELSE
                    po.status
            END AS status
        FROM 
            purchase_orders po
        LEFT JOIN 
            vendors v ON po.vendor_id = v.id
        WHERE 
            po.status IN ('received', 'overdue', 'paid')";

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

        } catch (\Exception $e) {
            error_log('Fetch purchase order bills failed: ' . $e->getMessage());
            return ['data' => [], 'meta' => []];
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

        } catch (\Exception $e) {
            error_log('Count purchase orders failed: ' . $e->getMessage());
            return 0;
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
                so.sent_to_kitchen,
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

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue('order_id', $orderId);

            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result && isset($result['product_order'])) {
                $result['product_order'] = json_decode($result['product_order'], true);
            }

            return $result;
        } catch (\Exception $e) {
            error_log('Fetch sales order failed: ' . $e->getMessage());
            return false;
        }
    }

    public function confirmSalesOrderPayment($orderId)
    {
        try {
            $this->db->beginTransaction();

            $query = "
                UPDATE sales_orders
                SET payment_status = 'paid'
                WHERE id = :order_id
                RETURNING *
            ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':order_id', $orderId);
            $stmt->execute();

            $sales = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$sales) {
                throw new \Exception("Sales order not found or update failed.");
            }

            $usersToNotify = BaseController::getUserByRole(['Admin']);

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
                    'entity_id' => $sales['id'] ?? null,
                    'entity_type' => "sales_order",
                    'title' => 'Sales Order Payment Confirmed',
                    'body' => $sales['order_id'] . ' payment has been confirmed'
                ];

                (new NotificationService())->sendNotification($notification);
            }

            $this->db->commit();

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error in confirmSalesOrderPayment: " . $e->getMessage());
            throw new \Exception("An error occurred while confirming sales order payment.");
        }
    }

    public function markBillAsPaid($billId)
    {
        try {
            $this->db->beginTransaction();

            $query = "
                UPDATE purchase_orders
                SET status = 'paid'
                WHERE id = :bill_id
                RETURNING *
            ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':bill_id', $billId);
            $stmt->execute();

            $bill = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$bill) {
                throw new \Exception("Bill not found or update failed.");
            }

            $usersToNotify = BaseController::getUserByRole(['Admin']);

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
                    'entity_id' => $bill['id'] ?? null,
                    'entity_type' => "purchase_order",
                    'title' => 'Bill Marked as Paid',
                    'body' => $bill['purchase_order_number'] . ' has been marked as paid'
                ];

                (new NotificationService())->sendNotification($notification);
            }

            $this->db->commit();

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error in markBillAsPaid: " . $e->getMessage());
            throw new \Exception("An error occurred while marking bill as paid.");
        }
    }

    public function getSalesOrdersToKitchen()
    {
        try {
            $fetchQuery = "
                SELECT id
                FROM sales_orders
                WHERE sent_to_kitchen = TRUE
                AND status = 'paid'
            ";

            $fetchStmt = $this->db->prepare($fetchQuery);
            $fetchStmt->execute();

            return $fetchStmt->fetchAll(\PDO::FETCH_COLUMN, 0);

        } catch (\Exception $e) {
            error_log("Error in getSalesOrdersToKitchen: " . $e->getMessage());
            return null;
        }
    }

}
