<?php

namespace Models;

use Database\Database;

class Vendor
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }


    public function create($data)
    {
        $query = "
			INSERT INTO vendors 
			(salutation, first_name, last_name, display_name, company_name, email, 
			work_phone, mobile_phone, address, social_media, payment_term_id, 
			currency_id, category_id, website) 
			VALUES 
			(:salutation, :first_name, :last_name, :display_name, :company_name, 
			:email, :work_phone, :mobile_phone, :address, :social_media, 
			:payment_term_id, :currency_id, :category_id, :website)
		";

        $stmt = $this->db->prepare($query);

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
        $stmt->bindParam(':payment_term_id', $data['payment_term_id']);
        $stmt->bindParam(':currency_id', $data['currency_id']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':website', $data['website']);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    public function updateVendor($id, $data)
    {
        $query = "
            UPDATE vendors 
            SET 
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
                payment_term_id = :payment_term_id,
                currency_id = :currency_id,
                category_id = :category_id,
                website = :website
            WHERE 
                id = :id
        ";

        $stmt = $this->db->prepare($query);

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
        $stmt->bindParam(':payment_term_id', $data['payment_term_id']);
        $stmt->bindParam(':currency_id', $data['currency_id']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':website', $data['website']);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function deleteVendor($id)
    {
        $query = "DELETE FROM vendors WHERE id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function getVendor($id)
    {
        $query = "SELECT * FROM vendors WHERE id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getVendors($filters = [])
    {
        $page = $filters['page'] ?? 1;
        $pageSize = $filters['page_size'] ?? 10;
        $offset = ($page - 1) * $pageSize;

        $query = "
            SELECT 
                v.id,
                v.salutation || ' ' || v.first_name || ' ' || v.last_name AS name,
                vc.name AS category,
                v.email,
                v.work_phone,
                v.address,
                COALESCE(SUM(t.amount), 0) AS total_transaction,
                v.balance,
                v.status
            FROM 
                vendors v
            LEFT JOIN 
                vendor_transactions t 
            ON 
                v.id = t.vendor_id
            LEFT JOIN 
                vendor_categories vc
            ON 
                v.category_id = vc.id
        ";

        $conditions = [];
        $params = [];

        if (!empty($filters['category_id'])) {
            $conditions[] = "v.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = "v.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $sortBy = $filters['sort_by'] ?? 'v.id';
        $sortOrder = strtoupper($filters['sort_order'] ?? 'DESC');

        if (!in_array($sortOrder, ['ASC', 'DESC'])) {
            $sortOrder = 'DESC';
        }

        $query .= "
            GROUP BY v.id, vc.name
            ORDER BY {$sortBy} {$sortOrder}
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

        $totalItems = $this->getTotalVendorCount($filters);

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

    private function getTotalVendorCount($filters = [])
    {
        $countQuery = "
            SELECT COUNT(DISTINCT v.id) AS total_vendors
            FROM vendors v
            LEFT JOIN vendor_transactions t ON v.id = t.vendor_id
            LEFT JOIN vendor_categories vc ON v.category_id = vc.id
        ";

        $conditions = [];
        $params = [];

        if (!empty($filters['category_id'])) {
            $conditions[] = "v.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = "v.status = :status";
            $params['status'] = $filters['status'];
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

    public function getVendorTransactions($vendorId)
    {
        $query = "
            SELECT 
                v.id AS vendor_id,
                v.display_name,
                v.email,
                v.work_phone,
                v.mobile_phone,
                vc.name AS vendor_category,
                c.code AS default_currency,
                pt.name AS payment_term,
                COALESCE(
                    SUM(
                        CASE WHEN vt.transaction_type = 'credit' THEN vt.amount ELSE 0 END
                    ), 0
                ) AS total_paid,
                COALESCE(
                    SUM(
                        CASE WHEN vt.transaction_type = 'debit' THEN vt.amount ELSE 0 END
                    ), 0
                ) AS total_outstanding,
                COALESCE(
                    JSON_AGG(
                        JSONB_BUILD_OBJECT(
                            'purchase_order_id', po.id,
                            'purchase_order_number', po.purchase_order_number,
                            'transaction_type', vt.transaction_type,
                            'transaction_created_at', vt.created_at,
                            'delivery_date', po.delivery_date,
                            'processed_by', u.name,
                            'emailed', vt.invoice_sent,
                            'purchase_order_status', po.status
                        )
                    ) FILTER (WHERE vt.id IS NOT NULL),
                    '[]'
                ) AS receivables
            FROM vendors v
            LEFT JOIN vendor_transactions vt ON v.id = vt.vendor_id
            LEFT JOIN purchase_orders po ON v.id = po.vendor_id
            LEFT JOIN users u ON po.processed_by = u.id
            LEFT JOIN vendor_categories vc ON v.category_id = vc.id
            LEFT JOIN currencies c ON v.currency_id = c.id
            LEFT JOIN payment_terms pt ON v.payment_term_id = pt.id
            WHERE v.id = :vendor_id
            GROUP BY 
                v.id, v.display_name, v.email, v.work_phone,
                vc.name, c.code, pt.name
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':vendor_id', $vendorId, \PDO::PARAM_INT);

        if ($stmt->execute()) {
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                $result['receivables'] = json_decode($result['receivables'], true);
                $result['total_paid'] = (float) $result['total_paid'];
                $result['total_outstanding'] = (float) $result['total_outstanding'];
            }

            return $result;
        }

        return false;
    }

}
