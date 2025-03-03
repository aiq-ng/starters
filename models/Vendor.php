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

        try {
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
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Error creating vendor");
        }
    }

    public function updateVendor($id, $data)
    {
        try {
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
                UPDATE vendors
                SET $setClauseString
                WHERE id = :id
                RETURNING id;
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Error updating vendor " . $e->getMessage());
        }
    }

    public function deleteVendor($vendorIds)
    {
        if (empty($vendorIds)) {
            return 0;
        }

        try {
            $vendorIds = is_array($vendorIds) ? $vendorIds : [$vendorIds];

            $placeholders = implode(',', array_fill(0, count($vendorIds), '?'));

            $stmt = $this->db->prepare("DELETE FROM vendors WHERE id IN ($placeholders)");
            $stmt->execute($vendorIds);

            return $stmt->rowCount();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Error deleting vendor");
        }
    }

    public function getVendor($id)
    {
        $query = "SELECT * FROM vendors WHERE id = :id";

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

    public function getVendors($filters = [])
    {
        try {
            $page = $filters['page'] ?? 1;
            $pageSize = $filters['page_size'] ?? 10;
            $offset = ($page - 1) * $pageSize;

            $query = "
                SELECT 
                    v.id,
                    CONCAT(v.first_name, ' ', v.last_name) AS name,
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

            if (!empty($filters['status']) && strtolower($filters['status']) !== 'all') {
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

            // Validate and sanitize sorting
            $allowedSortFields = [
                'v.id', 'name', 'category', 'v.email',
                'total_transaction', 'v.balance', 'v.status'
            ];

            $sortBy = $filters['sort_by'] ?? 'v.id';
            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'v.id';
            }

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
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return ['data' => [], 'meta' => []];
        }
    }

    private function getTotalVendorCount($filters = [])
    {
        try {
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

            if (!empty($filters['status']) && strtolower($filters['status']) !== 'all') {
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
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return 0;
        }
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
                c.code AS currency,
                SUM(COALESCE(vt.amount, 0)) FILTER (WHERE po.status = 'received') AS outstanding_receivables,
                SUM(COALESCE(vt.amount, 0)) FILTER (WHERE po.status = 'paid') AS payables,
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

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':vendor_id', $vendorId);

            if ($stmt->execute()) {
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($result) {
                    $result['transactions'] = !empty($result['transactions'])
                        ? json_decode($result['transactions'], true)
                        : [];

                    $result['social_media'] = !empty($result['social_media'])
                        ? json_decode($result['social_media'], true)
                        : [];
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
