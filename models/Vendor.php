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
        RETURNING id";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':salutation', $data['salutation'] ?? null);
        $stmt->bindValue(':first_name', $data['first_name'] ?? null);
        $stmt->bindValue(':last_name', $data['last_name'] ?? null);
        $stmt->bindValue(':display_name', $data['display_name'] ?? null);
        $stmt->bindValue(':company_name', $data['company_name'] ?? null);
        $stmt->bindValue(':email', $data['email'] ?? null);
        $stmt->bindValue(':work_phone', $data['work_phone'] ?? null);
        $stmt->bindValue(':mobile_phone', $data['mobile_phone'] ?? null);
        $stmt->bindValue(':address', $data['address'] ?? null);
        $stmt->bindValue(':social_media', $data['social_media'] ?? null);
        $stmt->bindValue(':payment_term_id', $data['payment_term_id'] ?? null);
        $stmt->bindValue(':currency_id', $data['currency_id'] ?? null);
        $stmt->bindValue(':category_id', $data['category_id'] ?? null);
        $stmt->bindValue(':website', $data['website'] ?? null);

        if ($stmt->execute()) {
            return $stmt->fetchColumn();
        }

        return null;
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

        $stmt->bindValue(':salutation', $data['salutation'] ?? null);
        $stmt->bindValue(':first_name', $data['first_name'] ?? null);
        $stmt->bindValue(':last_name', $data['last_name'] ?? null);
        $stmt->bindValue(':display_name', $data['display_name'] ?? null);
        $stmt->bindValue(':company_name', $data['company_name'] ?? null);
        $stmt->bindValue(':email', $data['email'] ?? null);
        $stmt->bindValue(':work_phone', $data['work_phone'] ?? null);
        $stmt->bindValue(':mobile_phone', $data['mobile_phone'] ?? null);
        $stmt->bindValue(':address', $data['address'] ?? null);
        $stmt->bindValue(':social_media', $data['social_media'] ?? null);
        $stmt->bindValue(':payment_term_id', $data['payment_term_id'] ?? null);
        $stmt->bindValue(':currency_id', $data['currency_id'] ?? null);
        $stmt->bindValue(':category_id', $data['category_id'] ?? null);
        $stmt->bindValue(':website', $data['website'] ?? null);
        $stmt->bindValue(':id', $id);

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

        if (!empty($filters['search'])) {
            $conditions[] = "
            (
                v.first_name ILIKE :search OR 
                v.last_name ILIKE :search OR 
                v.company_name ILIKE :search OR 
                v.display_name ILIKE :search OR 
                v.email ILIKE :search OR 
                v.address ILIKE :search OR 
                v.website ILIKE :search OR 
                v.social_media::TEXT ILIKE :search
            )
        ";
            $params['search'] = '%' . $filters['search'] . '%';
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
            'total_data' => (int) $totalItems,
            'total_pages' => ceil($totalItems / $pageSize),
            'page_size' => (int) $pageSize,
            'previous_page' => $page > 1 ? (int) $page - 1 : null,
            'current_page' => (int) $page,
            'next_page' => $page < ceil($totalItems / $pageSize) ? (int) $page + 1 : null,
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

        if (!empty($filters['search'])) {
            $conditions[] = "
            (
                v.first_name ILIKE :search OR 
                v.last_name ILIKE :search OR 
                v.company_name ILIKE :search OR 
                v.display_name ILIKE :search OR 
                v.email ILIKE :search OR 
                v.address ILIKE :search OR 
                v.website ILIKE :search OR 
                v.social_media::TEXT ILIKE :search
            )
        ";
            $params['search'] = '%' . $filters['search'] . '%';
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
                v.salutation,
                v.first_name,
                v.last_name,
                v.company_name,
                v.address,
                v.website,
                v.payment_term_id,
                v.currency_id,
                v.category_id,
                v.display_name,
                v.email,
                v.work_phone,
                v.mobile_phone,
                vc.name AS vendor_category,
                c.code AS default_currency,
                pt.name AS payment_term,
                v.social_media,
                JSONB_BUILD_OBJECT(
                    'currency', c.code,
                    'total_outstanding', COALESCE(
                        SUM(
                            CASE WHEN vt.transaction_type = 'debit' THEN vt.amount ELSE 0 END
                        ), 0
                    ),
                    'total_paid', COALESCE(
                        SUM(
                            CASE WHEN vt.transaction_type = 'credit' THEN vt.amount ELSE 0 END
                        ), 0
                    )
                ) AS receivables,
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
                ) AS transactions
            FROM vendors v
            LEFT JOIN vendor_transactions vt ON v.id = vt.vendor_id
            LEFT JOIN purchase_orders po ON v.id = po.vendor_id
            LEFT JOIN users u ON po.processed_by = u.id
            LEFT JOIN vendor_categories vc ON v.category_id = vc.id
            LEFT JOIN currencies c ON v.currency_id = c.id
            LEFT JOIN payment_terms pt ON v.payment_term_id = pt.id
            WHERE v.id = :vendor_id
            GROUP BY 
                v.id, v.display_name, v.email, v.work_phone, v.salutation,
                v.first_name, v.last_name, v.company_name, v.address,
                v.website, v.social_media, v.payment_term_id, v.currency_id,
                v.category_id, vc.name, c.code, pt.name
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':vendor_id', $vendorId);

        if ($stmt->execute()) {
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                $result['receivables'] = json_decode($result['receivables'], true);
                $result['transactions'] = json_decode($result['transactions'], true);
                $result['social_media'] = json_decode($result['social_media'], true);

            }

            return $result;
        }

        return false;
    }

}
