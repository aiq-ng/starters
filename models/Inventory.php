<?php

namespace Models;

use Database\Database;

class Inventory
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();

    }

    public function getInventory($filter = null)
    {
        $page = $filter['page'] ?? 1;
        $pageSize = $filter['page_size'] ?? 10;
        $order = $filter['order'] ?? 'created_at';
        $sort = $filter['sort'] ?? 'DESC';

        $sql = "
            SELECT
                i.id, i.name, i.media, i.price AS buying_price, 
                i.on_hand AS quantity, i.threshold_value, 
                item_stocks.expiry_date, i.sku, i.availability
            FROM items i
            LEFT JOIN item_stocks ON i.id = item_stocks.item_id
        ";

        $params = [];

        if (!empty($filter['availability'])) {
            $sql .= " WHERE i.availability = :filterAvailability";
            $params['filterAvailability'] = $filter['availability'];
        }

        $sql .= " GROUP BY i.id, i.name, i.media, i.price, i.threshold_value,
            item_stocks.expiry_date, i.sku, i.availability
        ";
        $sql .= " ORDER BY $order $sort";
        $sql .= " LIMIT :pageSize OFFSET :offset";

        $params['pageSize'] = $pageSize;
        $params['offset'] = ($page - 1) * $pageSize;

        $stmt = $this->db->prepare($sql);

        // Bind parameters
        $stmt->bindValue(':pageSize', $params['pageSize'], \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $params['offset'], \PDO::PARAM_INT);

        if (!empty($filter['availability'])) {
            $stmt->bindValue(
                ':filterAvailability',
                $params['filterAvailability'],
                \PDO::PARAM_STR
            );
        }

        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($results as &$item) {
            $item['media'] = !empty($item['media']) ? json_decode($item['media'], true) : null;
        }

        $total = $this->countInventory($filter);

        $meta = [
            'current_page' => $page,
            'last_page' => ceil($total / $pageSize),
            'total' => $total
        ];

        return [
            'inventory' => $results,
            'meta' => $meta
        ];
    }

    private function countInventory($filter = null)
    {
        $countSql = "
            SELECT COUNT(DISTINCT i.id) AS total_count
            FROM items i
            LEFT JOIN item_stocks ON i.id = item_stocks.item_id
        ";

        $params = [];

        if (!empty($filter['availability'])) {
            $countSql .= " WHERE i.availability = :filterAvailability";
            $params['filterAvailability'] = $filter['availability'];
        }

        $countStmt = $this->db->prepare($countSql);
        if (!empty($filter['availability'])) {
            $countStmt->bindParam(':filterAvailability', $params['filterAvailability']);
        }
        $countStmt->execute();

        return $countStmt->fetchColumn();
    }

    public function createItem($data, $mediaLinks = [])
    {
        try {
            $this->db->beginTransaction();

            $sql = "
                INSERT INTO items
                (name, description, unit_id, category_id,
                price, threshold_value, media, opening_stock, on_hand)
                VALUES (:name, :description, :unitId, :categoryId,
                :price, :threshold, :media, :openingStock, :onHand)
            ";

            $mediaLinks = json_encode($mediaLinks);

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':unitId', $data['unit_id']);
            $stmt->bindParam(':categoryId', $data['category_id']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':threshold', $data['threshold_value']);
            $stmt->bindParam(':media', $mediaLinks);
            $stmt->bindParam(':openingStock', $data['quantity']);
            $stmt->bindParam(':onHand', $data['quantity']);

            if (!$stmt->execute()) {
                throw new \Exception('Failed to insert item.');
            }

            $itemId = $this->db->lastInsertId();

            if (!$this->upsertItemStock($itemId, $data)) {
                throw new \Exception('Failed to insert item stock.');
                return false;
            }

            if (!$this->upsertItemRelationships($itemId, $data)) {
                throw new \Exception('Failed to insert item relationships.');
                return false;
            }

            $this->db->commit();

            return $itemId;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    public function updateItem($itemId, $data, $mediaLinks = [])
    {
        try {
            $this->db->beginTransaction();

            $sql = "
                UPDATE items
                SET name = :name, description = :description, unit_id = :unitId,
                category_id = :categoryId,price = :price,
                threshold_value = :threshold, media = :media,
                opening_stock = :openingStock, on_hand = :onHand
                WHERE id = :itemId
            ";

            $mediaLinks = json_encode($mediaLinks);

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':unitId', $data['unit_id']);
            $stmt->bindParam(':categoryId', $data['category_id']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':threshold', $data['threshold_value']);
            $stmt->bindParam(':media', $mediaLinks);
            $stmt->bindParam(':openingStock', $data['quantity']);
            $stmt->bindParam(':onHand', $data['quantity']);
            $stmt->bindParam(':itemId', $itemId);

            if (!$stmt->execute()) {
                throw new \Exception('Failed to update item.');
            }

            if (!$this->upsertItemStock($itemId, $data)) {
                throw new \Exception('Failed to update or insert item stock.');
            }

            if (!$this->upsertItemRelationships($itemId, $data)) {
                throw new \Exception('Failed to update or insert item relationships.');
            }

            $this->db->commit();

            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    private function upsertItemStock($itemId, $data)
    {
        $dateReceived = $data['date_received'] ?? date('Y-m-d');
        $stockSql = "
            INSERT INTO item_stocks (item_id, quantity, expiry_date, date_received)
            VALUES (:itemId, :quantity, :expiryDate, :dateReceived)
            ON CONFLICT (item_id, date_received)
            DO UPDATE SET
                quantity = EXCLUDED.quantity,
                expiry_date = EXCLUDED.expiry_date,
                date_received = EXCLUDED.date_received
        ";

        $stockStmt = $this->db->prepare($stockSql);
        $stockStmt->bindParam(':itemId', $itemId);
        $stockStmt->bindParam(':quantity', $data['quantity']);
        $stockStmt->bindParam(':expiryDate', $data['expiry_date']);
        $stockStmt->bindParam(':dateReceived', $dateReceived);

        return $stockStmt->execute();
    }

    private function upsertItemRelationships($itemId, $data)
    {
        if (!empty($data['vendor_id'])) {
            if (!$this->upsertItemStockVendor($itemId, $data['vendor_id'])) {
                return false;
            }
        }

        if (!empty($data['department_id'])) {
            if (!$this->upsertItemStockDepartment($itemId, $data['department_id'])) {
                return false;
            }
        }

        if (!empty($data['manufacturer_id'])) {
            if (!$this->upsertItemStockManufacturer($itemId, $data['manufacturer_id'])) {
                return false;
            }
        }

        return true;
    }

    private function upsertItemStockVendor($itemId, $vendorId)
    {
        $vendorSql = "
            INSERT INTO item_stock_vendors (stock_id, vendor_id)
            VALUES (:itemId, :vendorId)
            ON CONFLICT (stock_id, vendor_id) 
            DO UPDATE SET vendor_id = EXCLUDED.vendor_id
        ";

        $vendorStmt = $this->db->prepare($vendorSql);
        $vendorStmt->bindParam(':itemId', $itemId);
        $vendorStmt->bindParam(':vendorId', $vendorId);
        error_log("upsertItemStockVendor: $itemId, $vendorId");
        return $vendorStmt->execute();
    }

    private function upsertItemStockDepartment($itemId, $departmentId)
    {
        $departmentSql = "
            INSERT INTO item_stock_departments (stock_id, department_id)
            VALUES (:itemId, :departmentId)
            ON CONFLICT (stock_id, department_id) 
            DO UPDATE SET department_id = EXCLUDED.department_id
        ";

        $departmentStmt = $this->db->prepare($departmentSql);
        $departmentStmt->bindParam(':itemId', $itemId);
        $departmentStmt->bindParam(':departmentId', $departmentId);

        return $departmentStmt->execute();
    }

    private function upsertItemStockManufacturer($itemId, $manufacturerId)
    {
        $manufacturerSql = "
            INSERT INTO item_stock_manufacturers (stock_id, manufacturer_id)
            VALUES (:itemId, :manufacturerId)
            ON CONFLICT (stock_id, manufacturer_id) 
            DO UPDATE SET manufacturer_id = EXCLUDED.manufacturer_id
        ";

        $manufacturerStmt = $this->db->prepare($manufacturerSql);
        $manufacturerStmt->bindParam(':itemId', $itemId);
        $manufacturerStmt->bindParam(':manufacturerId', $manufacturerId);

        return $manufacturerStmt->execute();
    }

    public function getItem($itemId)
    {
        $sql = "
            SELECT 
                i.id AS item_id, 
                i.name AS item_name, 
                ic.name AS category, 
                d.name AS department,
                i.threshold_value, 
                its.expiry_date, 
                i.opening_stock, 
                i.on_hand AS remaining_stock,
                i.media
            FROM items i
            LEFT JOIN item_stocks its ON i.id = its.item_id
            LEFT JOIN item_stock_departments isd ON its.id = isd.stock_id
            LEFT JOIN departments d ON isd.department_id = d.id
            LEFT JOIN item_categories ic ON i.category_id = ic.id
            WHERE i.id = :itemId
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':itemId', $itemId, \PDO::PARAM_INT);

        try {
            $stmt->execute();
            $item = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($item) {
                $item['media'] = $item['media'] ? json_decode($item['media'], true) : [];
                return $item;
            }

            return null;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function deleteItem($itemId)
    {
        $sql = "
            DELETE FROM items
            WHERE id = :itemId
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':itemId', $itemId, \PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function adjustStock($itemId, $data)
    {
        if (!in_array($data['adjustment_type'], ['add', 'subtract'])) {
            throw new \Exception('Invalid operation, must be add or subtract');
        }

        $operation = $data['adjustment_type'] === 'add' ? '+' : '-';
        $quantity = $data['quantity'];

        $sql = "
            UPDATE items
            SET quantity = quantity $operation :quantity
            WHERE id = :itemId
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':quantity', $quantity, \PDO::PARAM_INT);
        $stmt->bindParam(':itemId', $itemId, \PDO::PARAM_INT);

        return $stmt->execute();
    }
}
