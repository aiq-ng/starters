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
        (customer_type, salutation, first_name, last_name, display_name, 
        company_name, email, work_phone, mobile_phone, address, social_media,
        website, currency_id, payment_term_id)
        VALUES 
        (:customer_type, :salutation, :first_name, :last_name, :display_name, 
        :company_name, :email, :work_phone, :mobile_phone, :address,
        :social_media, :website, :currency_id, :payment_term_id)
    ";

        $stmt = $this->db->prepare($query);

        // Bind parameters
        $stmt->bindParam(':customer_type', $data['customer_type']);
        $stmt->bindParam(':salutation', $data['salutation']);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':display_name', $data['display_name']);
        $stmt->bindParam(':company_name', $data['company_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':work_phone', $data['work_phone']);
        $stmt->bindParam(':mobile_phone', $data['mobile_phone']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':social_media', $data['social_media']);
        $stmt->bindParam(':website', $data['website']);
        $stmt->bindParam(':currency_id', $data['currency_id']);
        $stmt->bindParam(':payment_term_id', $data['payment_term_id']);

        // Execute query
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    public function updateCustomer($id, $data)
    {
        $query = "
            UPDATE customers
            SET 
                customer_type = :customer_type,
                salutation = :salutation,
                first_name = :first_name,
                last_name = :last_name,
                display_name = :display_name,
                company_name = :company_name,
                email = :email,
                work_phone = :work_phone,
                mobile_phone = :mobile_phone,
                address = :address,
                social_media = :social_media,
                website = :website,
                currency_id = :currency_id,
                payment_term_id = :payment_term_id
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':customer_type', $data['customer_type']);
        $stmt->bindParam(':salutation', $data['salutation']);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':display_name', $data['display_name']);
        $stmt->bindParam(':company_name', $data['company_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':work_phone', $data['work_phone']);
        $stmt->bindParam(':mobile_phone', $data['mobile_phone']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':social_media', $data['social_media']);
        $stmt->bindParam(':website', $data['website']);
        $stmt->bindParam(':currency_id', $data['currency_id']);
        $stmt->bindParam(':payment_term_id', $data['payment_term_id']);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function deleteCustomer($id)
    {
        $query = "DELETE FROM customers WHERE id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function getCustomer($id)
    {
        $query = "SELECT * FROM customers WHERE id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getCustomers($filters = [])
    {
        $page = $filters['page'] ?? 1;
        $pageSize = $filters['page_size'] ?? 10;
        $offset = ($page - 1) * $pageSize;

        $query = "
            SELECT 
                c.id,
                c.salutation || ' ' || c.first_name || ' ' || c.last_name AS name,
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

        if (!empty($filters['status'])) {
            $conditions[] = "c.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['company_name'])) {
            $conditions[] = "c.company_name LIKE :company_name";
            $params['company_name'] = '%' . $filters['company_name'] . '%';
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

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }

        if ($stmt->execute()) {
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $totalItems = $this->getTotalCustomerCount($filters);

            $meta = [
                'current_page' => (int) $page,
                'page_size' => (int) $pageSize,
                'total_data' => (int) $totalItems,
                'total_pages' => ceil($totalItems / $pageSize),
            ];

            return [
                'data' => $data,
                'meta' => $meta,
            ];
        }

        return [];
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

        if (!empty($filters['status'])) {
            $conditions[] = "c.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['company_name'])) {
            $conditions[] = "c.company_name LIKE :company_name";
            $params['company_name'] = '%' . $filters['company_name'] . '%';
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

    public function getCustomerTransactions($customerId)
    {
        $query = "
            SELECT 
                c.id AS customer_id,
                c.display_name,
                c.email,
                c.work_phone,
                c.mobile_phone,
                c.customer_type,
                cu.code AS default_currency,
                pt.name AS payment_term,
                JSONB_BUILD_OBJECT(
                    'currency', cu.code,
                    'total_outstanding', COALESCE(
                        SUM(
                            CASE WHEN ct.transaction_type = 'debit' THEN ct.amount ELSE 0 END
                        ), 0
                    ),
                    'total_paid', COALESCE(
                        SUM(
                            CASE WHEN ct.transaction_type = 'credit' THEN ct.amount ELSE 0 END
                        ), 0
                    )
                ) AS receivables,
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
                c.id, c.display_name, c.email, c.work_phone,
                c.mobile_phone, c.customer_type, c.balance, cu.code, pt.name
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':customer_id', $customerId, \PDO::PARAM_INT);

        if ($stmt->execute()) {
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                $result['receivables'] = json_decode($result['receivables'], true);
                $result['transactions'] = json_decode($result['transactions'], true);
            }

            return $result;
        }

        return false;
    }



}
