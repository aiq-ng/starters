<?php

namespace Models;

use Database\Database;

class Customer
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }


    public function create($data)
    {

        $query = "
            INSERT INTO customers 
            (customer_type, salutation, first_name, last_name, 
            company_name, email, work_phone, mobile_phone, address, social_media,
            website, currency_id, payment_term_id)
            VALUES 
            (:customer_type, :salutation, :first_name, :last_name, 
            :company_name, :email, :work_phone, :mobile_phone, :address,
            :social_media, :website, :currency_id, :payment_term_id)
            RETURNING id
        ";

        try {
            $stmt = $this->db->prepare($query);

            $stmt->bindValue(':customer_type', $data['customer_type'] ?? 'individual');
            $stmt->bindValue(':salutation', $data['salutation'] ?? null);
            $stmt->bindValue(':first_name', $data['first_name'] ?? null);
            $stmt->bindValue(':last_name', $data['last_name'] ?? null);
            $stmt->bindValue(':company_name', $data['company_name'] ?? null);
            $stmt->bindValue(':email', $data['email'] ?? null);
            $stmt->bindValue(':work_phone', $data['work_phone'] ?? null);
            $stmt->bindValue(':mobile_phone', $data['mobile_phone'] ?? $data['mobile'] ?? null);
            $stmt->bindValue(':address', $data['address'] ?? null);
            $stmt->bindValue(':social_media', $data['social_media'] ?? null);
            $stmt->bindValue(':website', $data['website'] ?? null);
            $stmt->bindValue(':currency_id', $data['currency_id'] ?? null);
            $stmt->bindValue(':payment_term_id', $data['payment_term_id'] ?? null);

            if ($stmt->execute()) {
                return $stmt->fetchColumn();
            }

            return false;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Error creating customer");
        }
    }

    public function updateCustomer($id, $data)
    {

        $filteredData = array_filter($data, function ($value) {
            return $value !== "" && $value !== null;
        });

        $setClauses = [];
        $params = [':id' => $id];

        foreach ($filteredData as $field => $value) {
            $setClauses[] = "$field = :$field";
            $params[":$field"] = $value;
        }

        if (empty($setClauses)) {
            return null;
        }

        $setClauseString = implode(', ', $setClauses);

        $query = "
            UPDATE customers
            SET $setClauseString
            WHERE id = :id
            RETURNING id;
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            throw new \Exception("Failed to update customer: " . $e->getMessage());
        }
    }

    public function deleteCustomer($customerIds)
    {
        if (empty($customerIds)) {
            return false;
        }

        $customerIds = is_array($customerIds) ? $customerIds : [$customerIds];

        $placeholders = implode(',', array_fill(0, count($customerIds), '?'));

        try {
            $stmt = $this->db->prepare("DELETE FROM customers WHERE id IN ($placeholders)");
            $stmt->execute($customerIds);

            return $stmt->rowCount();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Error deleting customer");
        }
    }

    public function getCustomer($id)
    {
        $query = "SELECT * FROM customers WHERE id = :id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getCustomers($filters = [])
    {
        $page = $filters['page'] ?? 1;
        $pageSize = $filters['page_size'] ?? 10;
        $offset = ($page - 1) * $pageSize;

        $query = "
            SELECT 
                c.id,
                CONCAT(c.first_name, ' ', c.last_name) AS name,
                c.company_name,
                c.email,
                c.work_phone,
                c.address,
                COALESCE(SUM(t.amount), 0) AS total_transaction,
                c.balance,
                c.status
            FROM 
                customers c
            LEFT JOIN 
                customer_transactions t 
            ON 
                c.id = t.customer_id
        ";

        $conditions = [];
        $params = [];

        if (!empty($filters['status']) && strtolower($filters['status']) !== 'all') {
            $conditions[] = "c.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['company_name'])) {
            $conditions[] = "c.company_name LIKE :company_name";
            $params['company_name'] = '%' . $filters['company_name'] . '%';
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $conditions[] = "
                (
                    c.first_name LIKE :search OR 
                    c.last_name LIKE :search OR 
                    c.company_name LIKE :search OR
                    c.display_name LIKE :search OR
                    c.email LIKE :search OR 
                    c.address LIKE :search OR
                    c.website LIKE :search OR
                    c.social_media::TEXT LIKE :search

                )
            ";
            $params['search'] = $searchTerm;
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= "
            GROUP BY 
                c.id, c.first_name, c.last_name, c.company_name, 
                c.email, c.work_phone, c.address, c.balance, c.status
        ";

        $sortBy = $filters['sort_by'] ?? 'c.id';
        $sortOrder = strtoupper($filters['sort_order'] ?? 'ASC');

        if (!in_array($sortOrder, ['ASC', 'DESC'])) {
            $sortOrder = 'ASC';
        }

        $query .= "
            ORDER BY {$sortBy} {$sortOrder}
            LIMIT :pageSize OFFSET :offset
        ";

        $params['pageSize'] = $pageSize;
        $params['offset'] = $offset;

        try {
            $stmt = $this->db->prepare($query);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
            }

            if ($stmt->execute()) {
                $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $totalItems = $this->getTotalCustomerCount($filters);

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

            return [];
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return ['data' => [], 'meta' => []];
        }
    }

    private function getTotalCustomerCount($filters = [])
    {
        $countQuery = "
            SELECT COUNT(DISTINCT c.id) AS total_customers
            FROM customers c
            LEFT JOIN customer_transactions t ON c.id = t.customer_id
        ";

        $conditions = [];
        $params = [];

        if (!empty($filters['status']) && strtolower($filters['status']) !== 'all') {
            $conditions[] = "c.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['company_name'])) {
            $conditions[] = "c.company_name LIKE :company_name";
            $params['company_name'] = '%' . $filters['company_name'] . '%';
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $conditions[] = "
                (
                    c.first_name LIKE :search OR 
                    c.last_name LIKE :search OR 
                    c.company_name LIKE :search OR
                    c.display_name LIKE :search OR
                    c.email LIKE :search OR 
                    c.address LIKE :search OR
                    c.website LIKE :search OR
                    c.social_media::TEXT LIKE :search
                )
            ";
            $params['search'] = $searchTerm;
        }

        if (!empty($conditions)) {
            $countQuery .= " WHERE " . implode(" AND ", $conditions);
        }

        try {
            $stmt = $this->db->prepare($countQuery);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
            }

            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    public function getCustomerTransactions($customerId)
    {
        $query = "
            SELECT 
                c.id AS customer_id,
                c.customer_type,
                c.salutation,
                c.first_name,
                c.last_name,
                c.company_name,
                c.address,
                c.payment_term_id,
                c.currency_id,
                c.display_name,
                c.email,
                c.work_phone,
                c.mobile_phone,
                c.social_media,
                cu.code AS default_currency,
                pt.name AS payment_term,
                SUM(COALESCE(ct.amount, 0)) 
                    FILTER (WHERE so.payment_status = 'paid') AS paid,
                SUM(COALESCE(ct.amount, 0)) 
                    FILTER (WHERE so.payment_status = 'unpaid') AS outstanding_receivables,
                COALESCE(
                    JSON_AGG(
                        JSONB_BUILD_OBJECT(
                            'transaction_type', ct.transaction_type,
                            'reference_number', ct.reference_number,
                            'invoice_number', so.invoice_number,
                            'order_id', so.order_id,
                            'processed_by', u.name,
                            'transaction_created_at', ct.created_at,
                            'emailed', ct.invoice_sent
                        )
                    ) FILTER (WHERE ct.id IS NOT NULL),
                    '[]'
                ) AS transactions
            FROM customers c
            LEFT JOIN customer_transactions ct ON c.id = ct.customer_id
            LEFT JOIN sales_orders so ON c.id = so.customer_id
            LEFT JOIN users u ON so.processed_by = u.id
            LEFT JOIN currencies cu ON c.currency_id = cu.id
            LEFT JOIN payment_terms pt ON c.payment_term_id = pt.id
            WHERE c.id = :customer_id
            GROUP BY 
                c.id, c.display_name, c.email, c.work_phone, c.customer_type,
                c.salutation, c.first_name, c.last_name, c.company_name,
                c.address, c.payment_term_id, c.currency_id, c.social_media,
                c.mobile_phone, cu.code, pt.name
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':customer_id', $customerId, \PDO::PARAM_STR);

            if ($stmt->execute()) {
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($result) {
                    $result['transactions'] = json_decode($result['transactions'] ?? '[]', true);
                    $result['social_media'] = json_decode($result['social_media'] ?? '[]', true);
                }

                return $result;
            }

            return false;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
