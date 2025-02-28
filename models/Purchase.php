<?php

namespace Models;

use Database\Database;

class Purchase extends Inventory
{
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }

    public function getPurchaseOrders($filters = [])
    {
        try {
            $page = $filters['page'] ?? 1;
            $pageSize = $filters['page_size'] ?? 10;

            $query = "
                SELECT
                    po.id,
                    po.order_sequence AS serial_number,
                    po.purchase_order_number,
                    po.reference_number,
                    po.invoice_number,
                    CONCAT_WS(' ', v.salutation, v.first_name, v.last_name) AS vendor_name,
                    po.created_at::DATE AS order_date,
                    po.delivery_date,
                    COALESCE(po.total, 0.00) AS total,
                    po.status,
                    CASE
                        WHEN po.status = 'issued' THEN 'Issued'
                        ELSE pt.name
                    END AS payment
                FROM purchase_orders po
                LEFT JOIN vendors v ON po.vendor_id = v.id
                LEFT JOIN payment_terms pt ON po.payment_term_id = pt.id
            ";

            $conditions = [];
            $params = [];

            if (!empty($filters['status']) && strtolower($filters['status']) !== 'all') {
                $conditions[] = "po.status = :status";
                $params['status'] = $filters['status'];
            }

            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $conditions[] = "po.created_at::DATE BETWEEN :start_date AND :end_date";
                $params['start_date'] = $filters['start_date'];
                $params['end_date'] = $filters['end_date'];
            } elseif (!empty($filters['start_date'])) {
                $conditions[] = "po.created_at::DATE >= :start_date";
                $params['start_date'] = $filters['start_date'];
            } elseif (!empty($filters['end_date'])) {
                $conditions[] = "po.created_at::DATE <= :end_date";
                $params['end_date'] = $filters['end_date'];
            }

            if (!empty($filters['search'])) {
                $conditions[] = "
                (po.purchase_order_number ILIKE :search 
                OR po.reference_number ILIKE :search
                OR po.invoice_number ILIKE :search 
                OR CONCAT_WS(' ', v.salutation, v.first_name, v.last_name) ILIKE :search)
            ";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            if ($conditions) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }

            $query .= " ORDER BY po.created_at DESC LIMIT :limit OFFSET :offset";

            $params['limit'] = $pageSize;
            $params['offset'] = ($page - 1) * $pageSize;

            $stmt = $this->db->prepare($query);

            foreach ($params as $key => $value) {
                $type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $type);
            }

            $stmt->execute();
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $totalItems = $this->getPurchaseOrdersCount($filters);

            $meta = [
                'total_data' => (int) $totalItems,
                'total_pages' => ceil($totalItems / $pageSize),
                'page_size' => (int) $pageSize,
                'previous_page' => $page > 1 ? $page - 1 : null,
                'current_page' => (int) $page,
                'next_page' => $page * $pageSize < $totalItems ? $page + 1 : null,
            ];

            return ['data' => $data, 'meta' => $meta];
        } catch (\Exception $e) {
            error_log('Error fetching purchase orders: ' . $e->getMessage());
            return ['data' => [], 'meta' => []];
        }
    }

    private function getPurchaseOrdersCount($filters = [])
    {
        try {
            $query = "
                SELECT COUNT(*) AS count
                FROM purchase_orders po
                LEFT JOIN vendors v ON po.vendor_id = v.id
            ";

            $conditions = [];
            $params = [];

            if (!empty($filters['status']) && strtolower($filters['status']) !== 'all') {
                $conditions[] = "po.status = :status";
                $params['status'] = $filters['status'];
            }

            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $conditions[] = "po.created_at::DATE BETWEEN :start_date AND :end_date";
                $params['start_date'] = $filters['start_date'];
                $params['end_date'] = $filters['end_date'];
            } elseif (!empty($filters['start_date'])) {
                $conditions[] = "po.created_at::DATE >= :start_date";
                $params['start_date'] = $filters['start_date'];
            } elseif (!empty($filters['end_date'])) {
                $conditions[] = "po.created_at::DATE <= :end_date";
                $params['end_date'] = $filters['end_date'];
            }

            if (!empty($filters['search'])) {
                $conditions[] = "
                (po.purchase_order_number ILIKE :search 
                OR po.reference_number ILIKE :search 
                OR po.invoice_number ILIKE :search 
                OR CONCAT_WS(' ', v.salutation, v.first_name, v.last_name) ILIKE :search)
            ";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            if ($conditions) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $type);
            }

            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log('Error fetching purchase orders count: ' . $e->getMessage());
            return 0;
        }
    }

    public function deletePurchaseOrder(array $purchaseOrderIds)
    {
        try {
            if (empty($purchaseOrderIds)) {
                return false;
            }

            $placeholders = implode(',', array_fill(0, count($purchaseOrderIds), '?'));

            $query = "
                DELETE FROM purchase_orders
                WHERE id IN ($placeholders)
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute($purchaseOrderIds);

            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log('Error deleting purchase order: ' . $e->getMessage());
            throw new \Exception("Failed to delete purchase order: " . $e->getMessage());
        }
    }



    public function createPurchase($data)
    {
        try {
            $purchaseOrderId = $this->insertPurchaseOrder($data);

            $this->insertPurchaseOrderItems($purchaseOrderId, $data['items']);

            return $this->getInvoiceDetails($purchaseOrderId);
        } catch (\Exception $e) {
            error_log('Error creating purchase order: ' . $e->getMessage());
            throw new \Exception("Failed to create purchase order: " . $e->getMessage());
        }
    }

    private function insertPurchaseOrder($data)
    {
        $query = "
            INSERT INTO purchase_orders (delivery_date, vendor_id, 
                branch_id, payment_term_id, subject, notes,
                terms_and_conditions, discount, shipping_charge, total, processed_by) 
            VALUES (:delivery_date, :vendor_id, :branch_id,
                :payment_term_id, :subject, :notes, :terms_and_conditions, 
                :discount, :shipping_charge, :total, :processed_by)
            RETURNING id;
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':delivery_date' => $data['delivery_date'] ?? null,
                ':vendor_id' => $data['vendor_id'] ?? null,
                ':branch_id' => $data['branch_id'] ?? null,
                ':payment_term_id' => $data['payment_term_id'] ?? null,
                ':subject' => $data['subject'] ?? null,
                ':notes' => $data['notes'] ?? null,
                ':terms_and_conditions' => $data['terms_and_conditions'] ?? null,
                ':discount' => $data['discount'] ?? null,
                ':shipping_charge' => $data['shipping_charge'] ?? null,
                ':total' => $data['total'] ?? null,
                ':processed_by' => $data['user_id'] ?? null,
            ]);

            $this->db->commit();

            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new \Exception("Failed to insert purchase order: " . $e->getMessage());
        }
    }

    private function insertPurchaseOrderItems($purchaseOrderId, $items)
    {
        $query = "
            INSERT INTO purchase_order_items (
                purchase_order_id, item_id, quantity, price, tax_id
            ) VALUES (
                :purchase_order_id, :item_id, :quantity, :price, :tax_id
            );
        ";

        try {
            $stmt = $this->db->prepare($query);

            foreach ($items as $item) {
                // Filter out fields with empty ("") values
                $filteredItem = array_filter($item, fn ($value) => $value !== "");

                if (empty($filteredItem['item_id']) && !empty($filteredItem['item_name'])) {
                    $newItemId = $this->createItem([
                        'name' => $filteredItem['item_name'],
                        'quantity' => 0,
                        'price' => $filteredItem['price'] ?? 0,
                    ]);

                    if (!$newItemId) {
                        throw new \Exception("Failed to create item: " .
                            $filteredItem['item_name']);
                    }

                    $filteredItem['item_id'] = $newItemId;
                }

                if (empty($filteredItem['item_id'])) {
                    throw new \Exception("Missing item_id for purchase order item.");
                }

                $stmt->execute([
                    ':purchase_order_id' => $purchaseOrderId,
                    ':item_id' => $filteredItem['item_id'],
                    ':quantity' => $filteredItem['quantity'] ?? null,
                    ':price' => $filteredItem['price'] ?? null,
                    ':tax_id' => $filteredItem['tax_id'] ?? null,
                ]);
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new \Exception("Failed to insert purchase order items: " .
                $e->getMessage());
        }
    }

    public function updatePurchaseOrder($purchaseOrderId, $data)
    {
        $this->db->beginTransaction();

        try {
            $this->updatePurchaseOrderDetails($purchaseOrderId, $data);
            $this->updatePurchaseOrderItems($purchaseOrderId, $data['items']);

            $this->db->commit();
            return $this->getInvoiceDetails($purchaseOrderId);
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('Error updating purchase order: ' . $e->getMessage());
            throw new \Exception("Failed to update purchase order: " . $e->getMessage());
        }
    }

    private function updatePurchaseOrderDetails($purchaseOrderId, $data)
    {
        unset($data['items']);

        $filteredData = array_filter($data, function ($value) {
            return $value !== "" && $value !== null;
        });

        $setClauses = [];
        $params = [':purchase_order_id' => $purchaseOrderId];

        foreach ($filteredData as $field => $value) {
            $setClauses[] = "$field = :$field";
            $params[":$field"] = $value;
        }

        if (empty($setClauses)) {
            return null;
        }

        $setClauseString = implode(', ', $setClauses);

        $query = "
            UPDATE purchase_orders
            SET $setClauseString
            WHERE id = :purchase_order_id
            RETURNING id;
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            throw new \Exception("Failed to update purchase order: " . $e->getMessage());
        }
    }

    private function updatePurchaseOrderItems($purchaseOrderId, $items)
    {
        try {
            $existingItemIds = $this->getPurchaseOrderItemIds($purchaseOrderId);
            $incomingItemIds = array_column($items, 'item_id');
            $itemsToDelete = array_diff($existingItemIds, $incomingItemIds);

            foreach ($itemsToDelete as $itemToDelete) {
                $this->deletePurchaseOrderItem($purchaseOrderId, $itemToDelete);
            }

            foreach ($items as $item) {
                $item = array_filter($item, fn ($value) => $value !== "" && $value !== null);

                if (in_array($item['item_id'], $existingItemIds)) {
                    $query = "
                    UPDATE purchase_order_items
                    SET 
                        quantity = COALESCE(:quantity, quantity),
                        price = COALESCE(:price, price),
                        tax_id = COALESCE(:tax_id, tax_id)
                    WHERE purchase_order_id = :purchase_order_id 
                    AND item_id = :item_id
                ";
                } else {
                    $query = "
                    INSERT INTO purchase_order_items (purchase_order_id, item_id, quantity, price, tax_id)
                    VALUES (:purchase_order_id, :item_id, :quantity, :price, :tax_id)
                ";
                }

                $stmt = $this->db->prepare($query);
                $stmt->execute([
                    ':purchase_order_id' => $purchaseOrderId,
                    ':item_id' => $item['item_id'],
                    ':quantity' => $item['quantity'] ?? null,
                    ':price' => $item['price'] ?? null,
                    ':tax_id' => $item['tax_id'] ?? null
                ]);
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Failed to update purchase order items: " . $e->getMessage());
        }
    }

    private function getPurchaseOrderItemIds($purchaseOrderId)
    {
        $query = "
            SELECT item_id
            FROM purchase_order_items
            WHERE purchase_order_id = :purchase_order_id
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute(['purchase_order_id' => $purchaseOrderId]);

            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    private function deletePurchaseOrderItem($purchaseOrderId, $itemId)
    {
        $query = "
            DELETE FROM purchase_order_items
            WHERE purchase_order_id = :purchase_order_id
            AND item_id = :item_id
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':purchase_order_id' => $purchaseOrderId,
                ':item_id' => $itemId
            ]);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Failed to delete purchase order item: " . $e->getMessage());
        }
    }

    public function getPurchaseOrder($purchaseOrderId)
    {
        $query = "
            SELECT
                po.id,
                po.vendor_id,
                po.branch_id,
                po.payment_term_id,
                po.subject,
                po.notes,
                po.terms_and_conditions,
                po.purchase_order_number,
                po.reference_number, 
                CONCAT_WS(' ', v.salutation, v.first_name, v.last_name) AS vendor_name, 
                po.created_at::DATE AS order_date, 
                po.delivery_date, 
                COALESCE(po.total, 0.00) AS total, 
                po.discount,
                po.shipping_charge,
                po.status,
                CASE
                    WHEN po.status = 'issued' THEN 'Issued'
                ELSE pt.name
                END AS payment
            FROM purchase_orders po
            LEFT JOIN vendors v ON po.vendor_id = v.id
            LEFT JOIN payment_terms pt ON po.payment_term_id = pt.id
            WHERE po.id = :purchase_order_id
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([':purchase_order_id' => $purchaseOrderId]);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                $result['items'] = $this->getPurchaseOrderItems($purchaseOrderId);
            }

            return $result;
        } catch (\Exception $e) {
            error_log('Error fetching purchase order: ' . $e->getMessage());
            return null;
        }
    }


    public function getInvoiceDetails($purchaseOrderId)
    {
        $query = "
            SELECT po.id,
                po.vendor_id,
                po.branch_id,
                po.payment_term_id,
                po.subject,
                po.notes,
                po.terms_and_conditions,
                po.purchase_order_number,
                po.reference_number, 
                CONCAT_WS(' ', v.salutation, v.first_name, v.last_name) AS vendor_name, 
                po.created_at::DATE AS order_date, 
                po.delivery_date, 
                COALESCE(po.total, 0.00) AS total, 
                po.discount,
                po.shipping_charge,
                po.status,
                CASE
                    WHEN po.status = 'issued' THEN 'Issued'
                ELSE pt.name
                END AS payment,
                json_agg(
                    json_build_object(
                        'item_id', poi.item_id,
                        'item_name', i.name,
                        'item_description', i.description,
                        'quantity', poi.quantity,
                        'price', poi.price,
                        'amount', poi.quantity * poi.price,
                        'tax_id', poi.tax_id,
                        'tax_rate', tr.rate
                    )
                ) AS items
            FROM purchase_orders po
            LEFT JOIN vendors v ON po.vendor_id = v.id
            LEFT JOIN purchase_order_items poi ON poi.purchase_order_id = po.id
            LEFT JOIN payment_terms pt ON po.payment_term_id = pt.id
            LEFT JOIN taxes tr ON poi.tax_id = tr.id
            LEFT JOIN items i ON poi.item_id = i.id
            WHERE po.id = :purchase_order_id
            GROUP BY po.id, po.purchase_order_number, po.reference_number,
                v.display_name, v.email, v.address, v.mobile_phone, v.balance, 
                po.discount, po.shipping_charge, po.notes, po.total, 
                po.created_at, po.delivery_date, po.status, pt.name, v.salutation,
                v.first_name, v.last_name, po.subject, po.terms_and_conditions
        ";

        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute([':purchase_order_id' => $purchaseOrderId]);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                if (!empty($result['items'])) {
                    $result['items'] = json_decode($result['items'], true);
                } else {
                    $result['items'] = [];
                }
                return $result;
            }

        } catch (\Exception $e) {
            error_log('Error fetching purchase order details: ' . $e->getMessage());
            throw new \Exception("Failed to fetch purchase order details: " . $e->getMessage());
        }
    }

    public function markAsReceived($purchaseOrderId)
    {
        try {
            $this->db->beginTransaction();

            $paymentTermId = $this->updatePurchaseOrderStatus($purchaseOrderId);

            if ($paymentTermId === null) {
                throw new \Exception('Purchase order status update failed.');
            }

            $items = $this->getPurchaseOrderItems($purchaseOrderId);

            $inventory = new Inventory();
            if ($items) {
                $vendorId = $items[0]['vendor_id'];

                foreach ($items as $item) {
                    if (!$inventory->createItemStock($item['item_id'], $item)) {
                        throw new \Exception('Failed to insert item stock.');
                    }
                }
            }

            $this->updatePaymentDueDate($paymentTermId, $purchaseOrderId);

            $this->db->commit();

            return $vendorId ?? true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('Error marking purchase order as received: ' . $e->getMessage());
            return false;
        }
    }

    public function updatePaymentDueDate($paymentTermId, $purchaseOrderId)
    {
        $query = "
            SELECT name FROM payment_terms
            WHERE id = :payment_term_id
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([':payment_term_id' => $paymentTermId]);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result && stripos($result['name'], 'receipt') !== false) {
                $updateQuery = "
                    UPDATE purchase_orders
                    SET payment_due_date = CURRENT_DATE
                    WHERE id = :purchase_order_id
                ";

                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->execute([':purchase_order_id' => $purchaseOrderId]);
            }
        } catch (\Exception $e) {
            error_log('Error updating payment due date: ' . $e->getMessage());
        }
    }

    public function updatePurchaseOrderStatus($purchaseOrderId)
    {
        try {
            $updateQuery = "
                UPDATE purchase_orders
                SET status = 'received',
                    date_received = CURRENT_DATE
                WHERE id = :purchase_order_id
                RETURNING payment_term_id
            ";

            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->execute([':purchase_order_id' => $purchaseOrderId]);

            $result = $updateStmt->fetch(\PDO::FETCH_ASSOC);

            return $result ? $result['payment_term_id'] : null;
        } catch (\Exception $e) {
            error_log('Error updating purchase order status: ' . $e->getMessage());
            throw new \Exception('Failed to update purchase order status: ' . $e->getMessage());
        }
    }

    public function getPurchaseOrderItems($purchaseOrderId)
    {
        try {
            $query = "
                SELECT poi.id, 
                    poi.item_id, 
                    poi.quantity, 
                    poi.price, 
                    poi.tax_id,
                    po.branch_id,
                    po.vendor_id,
                    v.display_name AS vendor_name
                FROM purchase_order_items poi
                LEFT JOIN purchase_orders po ON poi.purchase_order_id = po.id
                LEFT JOIN vendors v ON po.vendor_id = v.id
                WHERE poi.purchase_order_id = :purchase_order_id
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute([':purchase_order_id' => $purchaseOrderId]);

            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($results as &$result) {
                $result['expiry_date'] = date('Y-m-d', strtotime('+1 year'));
            }

            return $results;
        } catch (\Exception $e) {
            error_log('Error fetching purchase order items: ' . $e->getMessage());
            return [];
        }
    }
}
