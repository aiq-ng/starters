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

        $total = $this->countExpenses($conditions, $params);

        $sql = "SELECT 
                e.expense_id, 
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
                'current_page' => $page,
                'last_page' => ceil($total / $pageSize),
                'total' => $total
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

        $total = $this->countBills($conditions, $params);

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
                'current_page' => $page,
                'last_page' => ceil($total / $pageSize),
                'total' => $total
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

}
