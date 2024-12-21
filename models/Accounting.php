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
                    FROM purchase_orders
                ) AS total_purchases,
                (
                    SELECT COALESCE(SUM(total), 0)
                    FROM sales_orders
                ) - COALESCE((
                    SELECT SUM(total)
                    FROM purchase_orders
                ), 0) - COALESCE((
                    SELECT SUM(amount)
                    FROM expenses
                ), 0) AS Net_Profit,
                (
                    SELECT COALESCE(SUM(amount), 0)
                    FROM expenses
                ) AS outgoing,
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

        error_log('Params: ' . print_r($filter, true));

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
                'next_page' => (int) $page + 1,
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
                 FROM expenses e";

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

        if (!empty($filter['status'])) {
            $conditions[] = "po.status = :status";
            $params['status'] = $filter['status'];
        }

        if (!empty($filter['start_date']) && !empty($filter['end_date'])) {
            $conditions[] = "po.date_received BETWEEN :start_date AND :end_date";
            $params['start_date'] = $filter['start_date'];
            $params['end_date'] = $filter['end_date'];
        } elseif (!empty($filter['start_date'])) {
            $conditions[] = "po.date_received >= :start_date";
            $params['start_date'] = $filter['start_date'];
        } elseif (!empty($filter['end_date'])) {
            $conditions[] = "po.date_received <= :end_date";
            $params['end_date'] = $filter['end_date'];
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
                $stmt->bindParam(":$key", $value);
            }
            $stmt->bindParam(':pageSize', $pageSize, \PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);

            $stmt->execute();
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $meta = [
                'total_data' => (int) $totalItems,
                'total_pages' => ceil($totalItems / $pageSize),
                'page_size' => (int) $pageSize,
                'previous_page' => $page > 1 ? (int) $page - 1 : null,
                'current_page' => (int) $page,
                'next_page' => (int) $page + 1,
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
                 WHERE po.status IN ('received', 'overdue')";

        if (!empty($conditions)) {
            $countSql .= " AND " . implode(" AND ", $conditions);
        }

        try {
            $countStmt = $this->db->prepare($countSql);

            foreach ($params as $key => $value) {
                $countStmt->bindParam(":$key", $value);
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
        $stmt->bindValue('order_id', $orderId, \PDO::PARAM_INT);

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
        $stmt->bindValue('order_id', $orderId, \PDO::PARAM_INT);

        $stmt->execute();
    }

}
