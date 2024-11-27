<?php

namespace Models;

use Database\Database;

class Purchase
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    private function getTotalPurchasesCount()
    {
        $countQuery = "
            SELECT COUNT(DISTINCT p.id) AS total
            FROM purchases p
            JOIN suppliers s ON p.supplier_id = s.id
            LEFT JOIN purchase_items pi ON p.id = pi.purchase_id;
        ";

        $countStmt = $this->db->query($countQuery);
        return $countStmt->fetchColumn();
    }


    public function getPurchases($page = 1, $pageSize = 10)
    {
        $offset = ($page - 1) * $pageSize;

        $total = $this->getTotalPurchasesCount();

        $query = "
            SELECT 
                p.purchase_date,
                s.name AS supplier,
                COUNT(pi.product_name) AS items,
                COALESCE(SUM(pi.total_price), 0) AS total_cost,
                JSON_AGG(
                    JSON_BUILD_OBJECT(
                        'product', pi.product_name,
                        'quantity', pi.quantity,
                        'price_per_unit', pi.price_per_unit,
                        'total_price', pi.total_price
                    )
                )::TEXT AS products
            FROM 
                purchases p
            JOIN 
                suppliers s ON p.supplier_id = s.id
            LEFT JOIN 
                purchase_items pi ON p.id = pi.purchase_id
            GROUP BY 
                p.purchase_date, s.name
            ORDER BY 
                p.purchase_date DESC
            LIMIT :pageSize OFFSET :offset; -- Pagination
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':pageSize', $pageSize, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $purchases = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($purchases as &$purchase) {
            $purchase['products'] = json_decode($purchase['products']);
        }

        $meta = [
            'current_page' => $page,
            'last_page' => ceil($total / $pageSize),
            'total' => $total
        ];

        return [
            'data' => $purchases,
            'meta' => $meta
        ];
    }


    public function createPurchase($data)
    {
        $this->db->beginTransaction();

        try {
            $purchaseOrderId = $this->insertPurchaseOrder($data);

            $this->insertPurchaseOrderItems($purchaseOrderId, $data['items']);

            $this->db->commit();
            return $purchaseOrderId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function insertPurchaseOrder($data)
    {
        $query = "
            INSERT INTO purchase_orders (delivery_date, vendor_id, 
                branch_id, payment_term_id, subject, notes,
                terms_and_conditions, discount, shipping_charge, total) 
            VALUES (:delivery_date, :vendor_id, :branch_id,
                :payment_term_id, :subject, :notes, :terms_and_conditions, 
                :discount, :shipping_charge, :total)
            RETURNING id;
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':delivery_date' => $data['delivery_date'],
            ':vendor_id' => $data['vendor_id'],
            ':branch_id' => $data['branch_id'],
            ':payment_term_id' => $data['payment_term_id'],
            ':subject' => $data['subject'],
            ':notes' => $data['notes'],
            ':terms_and_conditions' => $data['terms_and_conditions'],
            ':discount' => $data['discount'],
            ':shipping_charge' => $data['shipping_charge'],
            ':total' => $data['total'],
        ]);

        return $stmt->fetchColumn();
    }

    private function insertPurchaseOrderItems($purchaseOrderId, $items)
    {
        $query = "
            INSERT INTO purchase_order_items (purchase_order_id, item_id, 
                quantity, price, tax_id)
            VALUES (:purchase_order_id, :item_id, :quantity, :price, :tax_id);
        ";

        $stmt = $this->db->prepare($query);

        foreach ($items as $item) {
            $stmt->execute([
                ':purchase_order_id' => $purchaseOrderId,
                ':item_id' => $item['item_id'],
                ':quantity' => $item['quantity'],
                ':price' => $item['price'],
                ':tax_id' => $item['tax_id'] ?? null,
            ]);
        }
    }

    private function logInventoryActivity($userId)
    {

        $activityQuery = "
            INSERT INTO inventory_activities (inventory_plan_id, user_id, action)
            VALUES (NULL, :user_id, 'purchase');
        ";

        $activityStmt = $this->db->prepare($activityQuery);
        $activityStmt->execute([
            ':user_id' => $userId,
        ]);
    }

}
