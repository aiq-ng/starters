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
        $purchaseDate = $data['date'];
        $supplierId = $data['supplier'];
        $items = $data['items'];
        $userId = $data['user_id'];

        $this->db->beginTransaction();

        try {
            $purchaseId = $this->insertPurchase($purchaseDate, $supplierId);
            $this->insertPurchaseItems($purchaseId, $items);
            $this->logInventoryActivity($userId);

            $this->db->commit();
            return $purchaseId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function insertPurchase($purchaseDate, $supplierId)
    {
        $query = "
            INSERT INTO purchases (purchase_date, supplier_id)
            VALUES (:purchase_date, :supplier_id) RETURNING id;
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':purchase_date' => $purchaseDate,
            ':supplier_id' => $supplierId
        ]);

        return $stmt->fetchColumn();
    }

    private function insertPurchaseItems($purchaseId, $items)
    {
        $itemQuery = "
            INSERT INTO purchase_items (purchase_id, product_name, quantity, price_per_unit)
            VALUES (:purchase_id, :product_id, :quantity, :price_per_unit);
        ";

        $itemStmt = $this->db->prepare($itemQuery);

        foreach ($items as $item) {
            $itemStmt->execute([
                ':purchase_id' => $purchaseId,
                ':product_id' => $item['product'],
                ':quantity' => $item['quantity'],
                ':price_per_unit' => $item['price_per_unit'],
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
