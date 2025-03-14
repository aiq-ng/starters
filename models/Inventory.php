<?php

namespace Models;

use Database\Database;
use Services\Utils;
use Services\BarcodeService;
use Services\NotificationService;
use Controllers\BaseController;

class Inventory
{
    private $db;
    private $barcodeService;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->barcodeService = new BarcodeService();

    }

    private function getItemCurrentQuantity($itemId)
    {
        try {
            $sql = "
                SELECT COALESCE(SUM(quantity), 0) AS total_quantity
                FROM item_stocks
                WHERE item_id = :itemId
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':itemId', $itemId);
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    public function getInventory($filter = null)
    {
        try {
            $page = $filter['page'] ?? 1;
            $pageSize = $filter['page_size'] ?? 10;
            $order = $filter['order'] ?? 'i.id';
            $sort = $filter['sort'] ?? 'DESC';
            $search = $filter['search'] ?? null;

            $sql = "
                SELECT
                    i.id, 
                    i.name, 
                    CONCAT(COALESCE(SUM(item_stocks.quantity), 0), ' ', u.abbreviation) AS quantity,
                    CONCAT(i.threshold_value, ' ', u.abbreviation) AS threshold_value,
                    i.price AS buying_price, 
                    MAX(item_stocks.expiry_date) AS expiry_date,
                    i.sku,
                    i.barcode,
                    i.availability, 
                    i.media
                FROM item_stocks
                JOIN items i ON item_stocks.item_id = i.id
                LEFT JOIN units u ON i.unit_id = u.id
            ";

            $conditions = [];
            $params = [];

            if (!empty($filter['availability']) && strtolower($filter['availability']) !== 'all') {
                $conditions[] = "i.availability = :filterAvailability";
                $params['filterAvailability'] = $filter['availability'];
            }

            if (!empty($search)) {
                $conditions[] = "(i.name ILIKE :search OR i.description ILIKE :search OR i.sku ILIKE :search)";
                $params['search'] = '%' . $search . '%';
            }

            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }

            $sql .= "
                GROUP BY 
                    i.id, i.name, i.threshold_value, i.price,
                    i.sku, i.availability, i.media, u.abbreviation
                ORDER BY $order $sort
                LIMIT :pageSize OFFSET :offset
            ";

            $params['pageSize'] = $pageSize;
            $params['offset'] = ($page - 1) * $pageSize;

            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':pageSize', $params['pageSize'], \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $params['offset'], \PDO::PARAM_INT);

            if (!empty($filter['availability']) && strtolower($filter['availability']) !== 'all') {
                $stmt->bindValue(':filterAvailability', $params['filterAvailability'], \PDO::PARAM_STR);
            }

            if (!empty($search)) {
                $stmt->bindValue(':search', $params['search'], \PDO::PARAM_STR);
            }

            $stmt->execute();

            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($results as &$item) {
                $item['media'] = !empty($item['media'])
                    ? json_decode($item['media'], true)
                    : null;

                if (empty($item['barcode']) && !empty($item['sku'])) {
                    try {
                        $barcode = $this->barcodeService->generateBarcode(
                            $item['sku'],
                            $item['id']
                        );
                        $this->barcodeService->updateItemBarcodeInDb(
                            $item['id'],
                            $barcode
                        );
                        $item['barcode'] = json_decode($barcode, true);
                    } catch (\Throwable $e) {
                        error_log('Barcode generation failed: ' . $e->getMessage());
                        $item['barcode'] = null; // Fallback
                    }
                } elseif (!empty($item['barcode'])) {
                    $item['barcode'] = json_decode($item['barcode'], true);
                }
            }

            $totalItems = $this->countInventory($filter);

            $meta = [
                'total_data' => (int) $totalItems,
                'total_pages' => ceil($totalItems / $pageSize),
                'page_size' => (int) $pageSize,
                'previous_page' => $page > 1 ? (int) $page - 1 : null,
                'current_page' => (int) $page,
                'next_page' => $page + 1 <= ceil($totalItems / $pageSize) ? (int) $page + 1 : null,
            ];

            return [
                'inventory' => $results,
                'meta' => $meta
    ];
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return ['inventory' => [], 'meta' => []];
        }
    }

    private function countInventory($filter = null)
    {
        try {
            $countSql = "
                SELECT COUNT(DISTINCT i.id) AS total_count
                FROM item_stocks
                JOIN items i ON item_stocks.item_id = i.id
                LEFT JOIN units u ON i.unit_id = u.id
            ";

            $conditions = [];
            $params = [];

            if (!empty($filter['availability'])) {
                $conditions[] = "i.availability = :filterAvailability";
                $params['filterAvailability'] = $filter['availability'];
            }

            if (!empty($filter['search'])) {
                $conditions[] = "(i.name ILIKE :search OR i.description ILIKE :search OR i.sku ILIKE :search)";
                $params['search'] = '%' . $filter['search'] . '%';
            }

            if (!empty($conditions)) {
                $countSql .= " WHERE " . implode(' AND ', $conditions);
            }

            $countStmt = $this->db->prepare($countSql);

            if (!empty($filter['availability'])) {
                $countStmt->bindValue(':filterAvailability', $params['filterAvailability'], \PDO::PARAM_STR);
            }

            if (!empty($filter['search'])) {
                $countStmt->bindValue(':search', $params['search'], \PDO::PARAM_STR);
            }

            $countStmt->execute();

            return $countStmt->fetchColumn();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    public function getItem($itemId)
    {
        $sql = "
            SELECT
                i.*,
                isd.department_id,
                isv.vendor_id,
                ism.manufacturer_id,
                isb.branch_id,
                STRING_AGG(DISTINCT ic.name, ', ') AS category,
                STRING_AGG(DISTINCT d.name, ', ') AS department,
                stock_data.expiry_date,
                COALESCE(stock_data.total_quantity, 0) AS quantity
            FROM items i
            LEFT JOIN (
                SELECT item_id, MAX(expiry_date) AS expiry_date, SUM(quantity) AS total_quantity
                FROM item_stocks
                WHERE item_id = :itemId
                GROUP BY item_id
            ) stock_data ON i.id = stock_data.item_id
            LEFT JOIN item_stocks its ON i.id = its.item_id
            LEFT JOIN item_stock_departments isd ON its.id = isd.stock_id
            LEFT JOIN item_stock_vendors isv ON its.id = isv.stock_id
            LEFT JOIN item_stock_manufacturers ism ON its.id = ism.stock_id
            LEFT JOIN item_stock_branches isb ON its.id = isb.stock_id
            LEFT JOIN departments d ON isd.department_id = d.id
            LEFT JOIN item_categories ic ON i.category_id = ic.id
            WHERE i.id = :itemId
            GROUP BY i.id, i.name, i.threshold_value, i.opening_stock, i.media,
                isd.department_id, isv.vendor_id, ism.manufacturer_id, isb.branch_id,
                stock_data.expiry_date, stock_data.total_quantity
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':itemId', $itemId);

        try {
            $stmt->execute();
            $item = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($item) {
                $item['media'] = $item['media'] ? json_decode($item['media'], true) : [];
                return $item;
            }

            return null;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function deleteItem($itemIds)
    {
        try {
            if (empty($itemIds)) {
                return 0;
            }

            $itemIds = is_array($itemIds) ? $itemIds : [$itemIds];

            $placeholders = implode(',', array_fill(0, count($itemIds), '?'));

            $stmt = $this->db->prepare("DELETE FROM items WHERE id IN ($placeholders)");
            $stmt->execute($itemIds);

            return $stmt->rowCount();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception('Failed to delete item(s).');
        }
    }


    public function createItem($data, $mediaLinks = [], $notify = true)
    {
        try {
            $this->db->beginTransaction();

            $mediaLinks = json_encode($mediaLinks);

            $sql = "
                INSERT INTO items (name, description, unit_id, category_id, price, 
                    threshold_value, media, opening_stock, sku)
                VALUES (:name, :description, :unitId, :categoryId, :price, 
                    :threshold, :media, :openingStock, :sku)
                ON CONFLICT (name) DO UPDATE SET
                    description = EXCLUDED.description,
                    unit_id = EXCLUDED.unit_id,
                    category_id = EXCLUDED.category_id,
                    price = EXCLUDED.price,
                    threshold_value = EXCLUDED.threshold_value,
                    media = EXCLUDED.media,
                    opening_stock = EXCLUDED.opening_stock,
                    sku = EXCLUDED.sku,
                    updated_at = CURRENT_TIMESTAMP
                RETURNING id;
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':name', $data['name'] ?? null);
            $stmt->bindValue(':description', $data['description'] ?? null);
            $stmt->bindValue(':unitId', $data['unit_id'] ?? null);
            $stmt->bindValue(':categoryId', $data['category_id'] ?? null);
            $stmt->bindValue(':price', $data['price'] ?? null);
            $stmt->bindValue(':threshold', $data['threshold_value'] ?? null);
            $stmt->bindValue(':media', $mediaLinks);
            $stmt->bindValue(':openingStock', $data['quantity'] ?? null);
            $stmt->bindValue(':sku', $data['sku'] ?? null);

            if (!$stmt->execute()) {
                throw new \Exception('Failed to insert/update item.');
            }

            $itemId = $stmt->fetchColumn();

            if (!$this->createItemStock($itemId, $data, $notify)) {
                throw new \Exception('Failed to insert/update item stock.');
            }

            $this->db->commit();

            return $itemId;

        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception('Failed to create or update item. ' . $e->getMessage());
        }
    }

    public function createItemStock($itemId, $data, $notify = true)
    {
        $dateReceived = $data['date_received'] ?? date('Y-m-d');

        try {
            $stockSql = "
                INSERT INTO item_stocks (item_id, quantity, expiry_date, date_received)
                VALUES (:itemId, :quantity, :expiryDate, :dateReceived)
                RETURNING id
            ";

            $stockStmt = $this->db->prepare($stockSql);
            $stockStmt->bindParam(':itemId', $itemId);
            $stockStmt->bindParam(':quantity', $data['quantity']);
            $stockStmt->bindParam(':expiryDate', $data['expiry_date']);
            $stockStmt->bindParam(':dateReceived', $dateReceived);

            if (!$stockStmt->execute()) {
                throw new \Exception('Failed to insert item stock.');
            }

            $stockId = $stockStmt->fetchColumn();

            if (!$this->upsertItemRelationships([$stockId], $data)) {
                throw new \Exception('Failed to insert item relationships.');
            }

            $this->checkItemAvailability($itemId, $notify);

            return $stockId;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception('Failed to create item stock.');
        }
    }

    public function updateItem($itemId, array $data, array $mediaLinks = []): bool
    {
        try {

            $this->db->beginTransaction();

            $fields = [];
            $bindings = [':itemId' => $itemId];

            foreach ($data as $key => $value) {
                if ($key === 'quantity' ||
                    $key === 'manager_id' ||
                    $key === 'source_id' ||
                    $key === 'source_department_id') {
                    continue; // Skip quantity for now, handle stock separately
                }
                $param = ":$key";
                $fields[] = "$key = $param";
                $bindings[$param] = $value;
            }

            if (!empty($mediaLinks)) {
                $bindings[':media'] = json_encode($mediaLinks);
                $fields[] = "media = :media";
            }

            $sql = "UPDATE items SET " . implode(", ", $fields) . " WHERE id = :itemId";
            $stmt = $this->db->prepare($sql);

            foreach ($bindings as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            if (!$stmt->execute()) {
                throw new \Exception('Failed to update item.');
            }

            // Handle stock quantity updates
            if (isset($data['quantity'])) {
                $current_quantity = $this->getItemCurrentQuantity($itemId);
                $difference = $data['quantity'] - $current_quantity;

                if ($difference !== 0) {
                    $adjustmentType = $difference < 0 ? 'subtraction' : 'addition';
                    $sourceType = $difference < 0 ? 'user' : 'vendor';

                    error_log($difference < 0 ? "Subtracting stock" : "Adding stock");

                    $stockIds = $this->adjustStock($itemId, [
                        'quantity' => abs($difference),
                        'adjustment_type' => $adjustmentType,
                        'description' => 'Edit item',
                        'manager_id' => $data['manager_id'] ?? null,
                        'source_id' => $data['source_id'] ?? null,
                        'source_department_id' => $data['source_department_id'] ?? null,
                        'source_type' => $sourceType,
                    ]);

                    // Update relationships if stock was adjusted
                    if (!$this->upsertItemRelationships($stockIds, $data)) {
                        throw new \Exception('Failed to update item relationships.');
                    }
                } else {
                    error_log("No stock change required");
                }
            }

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            throw new \Exception('Failed to update item.');
        }
    }

    private function upsertItemRelationships(array $stockIds, $data)
    {
        foreach ($stockIds as $stockId) {
            if (!empty($data['vendor_id'])) {
                if (!$this->upsertItemStockVendor($stockId, $data['vendor_id'])) {
                    return false;
                }
            }

            if (!empty($data['department_id'])) {
                if (!$this->upsertItemStockDepartment($stockId, $data['department_id'])) {
                    return false;
                }
            }

            if (!empty($data['manufacturer_id'])) {
                if (!$this->upsertItemStockManufacturer($stockId, $data['manufacturer_id'])) {
                    return false;
                }
            }

            if (!empty($data['branch_id'])) {
                if (!$this->upsertItemStockBranch($stockId, $data['branch_id'])) {
                    return false;
                }
            }
        }

        return true;
    }

    private function upsertItemStockVendor($stockId, $vendorId)
    {
        try {
            $vendorSql = "
                INSERT INTO item_stock_vendors (stock_id, vendor_id)
                VALUES (:stockId, :vendorId)
                ON CONFLICT (stock_id, vendor_id) 
                DO UPDATE SET vendor_id = EXCLUDED.vendor_id
            ";

            $vendorStmt = $this->db->prepare($vendorSql);
            $vendorStmt->bindParam(':stockId', $stockId);
            $vendorStmt->bindParam(':vendorId', $vendorId);

            return $vendorStmt->execute();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    private function upsertItemStockDepartment($stockId, $departmentId)
    {
        try {
            $departmentSql = "
                INSERT INTO item_stock_departments (stock_id, department_id)
                VALUES (:stockId, :departmentId)
                ON CONFLICT (stock_id, department_id) 
                DO UPDATE SET department_id = EXCLUDED.department_id
            ";

            $departmentStmt = $this->db->prepare($departmentSql);
            $departmentStmt->bindParam(':stockId', $stockId);
            $departmentStmt->bindParam(':departmentId', $departmentId);

            return $departmentStmt->execute();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    private function upsertItemStockManufacturer($stockId, $manufacturerId)
    {
        try {
            $manufacturerSql = "
                INSERT INTO item_stock_manufacturers (stock_id, manufacturer_id)
                VALUES (:stockId, :manufacturerId)
                ON CONFLICT (stock_id, manufacturer_id) 
                DO UPDATE SET manufacturer_id = EXCLUDED.manufacturer_id
            ";

            $manufacturerStmt = $this->db->prepare($manufacturerSql);
            $manufacturerStmt->bindParam(':stockId', $stockId);
            $manufacturerStmt->bindParam(':manufacturerId', $manufacturerId);

            return $manufacturerStmt->execute();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    private function upsertItemStockBranch($stockId, $branchId)
    {
        try {
            $branchSql = "
                INSERT INTO item_stock_branches (stock_id, branch_id)
                VALUES (:stockId, :branchId)
                ON CONFLICT (stock_id, branch_id) 
                DO UPDATE SET branch_id = EXCLUDED.branch_id
            ";

            $branchStmt = $this->db->prepare($branchSql);
            $branchStmt->bindParam(':stockId', $stockId);
            $branchStmt->bindParam(':branchId', $branchId);

            return $branchStmt->execute();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }


    public function adjustStock($itemId, $data)
    {
        if (!in_array($data['adjustment_type'], ['addition', 'subtraction'])) {
            throw new \Exception('Invalid operation, must be add or subtract');
        }

        $quantity = $data['quantity'];
        $affectedStockIds = [];

        try {
            $this->db->beginTransaction();

            if ($data['adjustment_type'] === 'subtraction') {
                $affectedStockIds = $this->subtractStockFIFO($itemId, $quantity, $data);
            }

            if ($data['adjustment_type'] === 'addition') {
                $newStockId = $this->createItemStock($itemId, $data);

                $this->insertStockAdjustment(
                    $newStockId,
                    $quantity,
                    $data['adjustment_type'],
                    $data['description'] ?? null,
                    $data['source_id'] ?? null,
                    $data['source_department_id'] ?? null,
                    $data['manager_id'] ?? null
                );

                $affectedStockIds[] = $newStockId;
            }

            $this->db->commit();

            $item = $this->getItem($itemId);
            $usersToNotify = BaseController::getUserByRole('Admin');

            if (empty($usersToNotify)) {
                throw new \Exception("No Admin user found for notification.");
            }

            $adjustmentText = $data['adjustment_type'] === 'addition'
                ? "increased by $quantity"
                : "reduced by $quantity";

            $title = 'Stock Adjustment';
            $body = "The stock of {$item['name']} has been $adjustmentText.";

            foreach ($usersToNotify as $userToNotify) {
                if (!isset($userToNotify['id'])) {
                    continue;
                }

                $notificationData = [
                    'user_id' => $userToNotify['id'],
                    'event' => 'notification',
                    'entity_id' => $itemId,
                    'entity_type' => 'items',
                    'title' => $title,
                    'body' => $body,
                ];

                (new NotificationService())->sendNotification($notificationData);
            }
            return $affectedStockIds;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            throw new \Exception('Failed to adjust stock.');
        }
    }

    private function checkItemAvailability($itemId, $notify = true)
    {
        try {
            $sql = "
                SELECT availability, name 
                FROM items 
                WHERE id = :itemId
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':itemId', $itemId, \PDO::PARAM_INT);
            $stmt->execute();

            $item = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$item) {
                throw new \Exception("Item not found.");
            }

            if ($notify === false) {
                return;
            }

            if (in_array($item['availability'], ['out of stock', 'low stock'], true)) {
                $usersToNotify = BaseController::getUserByRole(['Admin']);

                if (empty($usersToNotify)) {
                    throw new \Exception("Admin user not found for notification.");
                }

                $statusText = $item['availability'] === 'out of stock'
                    ? 'is currently out of stock'
                    : 'is running low on stock';

                $title = 'Stock Alert: ' . $item['name'];
                $body = "Attention: The item '{$item['name']}' $statusText. Please take action.";

                foreach ($usersToNotify as $userToNotify) {
                    if (!isset($userToNotify['id'])) {
                        continue;
                    }

                    $notification = [
                        'user_id' => $userToNotify['id'],
                        'event' => 'notification',
                        'entity_id' => $itemId,
                        'entity_type' => "items",
                        'title' => $title,
                        'body' => $body
                    ];

                    (new NotificationService())->sendNotification($notification);
                }
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    private function subtractStockFIFO($itemId, $quantity, $data)
    {
        try {
            $affectedStockIds = [];
            $stocks = $this->getStocksByItemId($itemId);

            foreach ($stocks as $stock) {
                error_log("Stock: " . json_encode($stock));
                if ($quantity <= 0) {
                    break;
                }

                $stockId = $stock['id'];
                $stockQuantity = $stock['quantity'];

                if ($stockQuantity > 0) {
                    $subtractAmount = min($quantity, $stockQuantity);
                    $quantity -= $subtractAmount;

                    $updateSql = "
                    UPDATE item_stocks
                    SET quantity = quantity - :subtractAmount
                    WHERE id = :stockId
                ";
                    $updateStmt = $this->db->prepare($updateSql);
                    $updateStmt->bindValue(':subtractAmount', $subtractAmount);
                    $updateStmt->bindValue(':stockId', $stockId);

                    if ($updateStmt->execute()) {
                        $affectedStockIds[] = $stockId;

                        $this->insertStockAdjustment(
                            $stockId,
                            $subtractAmount,
                            $data['adjustment_type'],
                            $data['description'] ?? null,
                            $data['source_id'] ?? null,
                            $data['source_department_id'] ?? null,
                            $data['manager_id'] ?? null
                        );
                    }
                }
            }

            if ($quantity > 0) {
                throw new \Exception('Insufficient stock to complete the operation.');
            }

            $this->checkItemAvailability($itemId);

            return $affectedStockIds;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    private function insertStockAdjustment(
        $stockId,
        $quantity,
        $adjustmentType,
        $description,
        $sourceId,
        $sourceDepartmentId,
        $managerId
    ) {
        try {
            $query = "
                INSERT INTO item_stock_adjustments
                (stock_id, quantity, adjustment_type,
                description, source_id, source_department_id, 
                manager_id, source_type)
                VALUES (:stockId, :quantity, :adjustmentType,
                :description, :source_id, :source_department_id, 
                :manager_id, 'user')
            ";
            $stmt = $this->db->prepare($query);

            $stmt->bindValue(':stockId', $stockId);
            $stmt->bindValue(':quantity', $quantity);
            $stmt->bindValue(':adjustmentType', $adjustmentType);
            $stmt->bindValue(':description', $description);
            $stmt->bindValue(':source_id', $sourceId);
            $stmt->bindValue(':source_department_id', $sourceDepartmentId);
            $stmt->bindValue(':manager_id', $managerId);

            if (!$stmt->execute()) {
                throw new \Exception('Failed to log stock adjustment.');
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }

    }

    private function getStocksByItemId($itemId)
    {
        try {
            $sql = "
                SELECT id, quantity, stock_code
                FROM item_stocks
                WHERE item_id = :itemId
                AND quantity > 0
                ORDER BY date_received ASC, id ASC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':itemId', $itemId, \PDO::PARAM_STR);
            $stmt->execute();

            error_log("getStocksByItemId: $itemId");

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }


    public function getAdjustmentHistory($itemId)
    {
        try {
            $sql = "
            SELECT 
                isa.id,
                isa.adjustment_type,
                manager.name AS manager,
                d.name AS department,
                CASE 
                    WHEN isa.adjustment_type = 'addition' THEN isa.quantity
                    ELSE NULL
                END AS added_quantity,
                CASE 
                    WHEN isa.adjustment_type = 'subtraction' THEN isa.quantity
                    ELSE NULL
                END AS reduced_quantity,
                CASE 
                    WHEN isa.source_type = 'user' THEN collector.name
                    ELSE NULL
                END AS collector,
                CASE
                    WHEN isa.source_type = 'vendor' THEN CONCAT(
                        vendors.salutation, ' ', vendors.first_name, ' ',
                        vendors.last_name
                    )
                    ELSE NULL
                END AS vendor,
                isa.description,
                isa.created_at,
                COALESCE(
                    json_agg(
                        DISTINCT jsonb_strip_nulls(
                            jsonb_build_object(
                                'id', c.id,
                                'user_id', c.user_id,
                                'name', u.firstname || ' ' || u.lastname,
                                'avatar', u.avatar_url,
                                'role', r.name,
                                'parent_id', c.parent_id,
                                'comment', c.comment,
                                'created_at', c.created_at
                            )
                        )
                    ) FILTER (WHERE c.id IS NOT NULL), '[]'::json
                ) AS comments            
            FROM item_stock_adjustments isa
            JOIN users manager ON isa.manager_id = manager.id
            LEFT JOIN users collector ON isa.source_type = 'user' AND isa.source_id = collector.id
            LEFT JOIN departments d ON isa.source_department_id = d.id
            LEFT JOIN vendors ON isa.source_type = 'vendor' AND isa.source_id = vendors.id
            LEFT JOIN comments c ON c.entity_id = isa.id AND c.entity_type = 'item_stock_adjustment'
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE isa.stock_id IN (
                SELECT id
                FROM item_stocks
                WHERE item_id = :itemId
            )
            GROUP BY isa.id, manager.name, d.name, vendors.salutation,
                vendors.first_name, vendors.last_name, collector.name
            ORDER BY isa.created_at DESC
        ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':itemId', $itemId, \PDO::PARAM_STR);
            $stmt->execute();

            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($data as &$row) {
                $row['comments'] = json_decode($row['comments'], true);
            }

            $filteredData = Utils::filterOutNull($data);

            return $filteredData ?? [];
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getItemPricesByDay($itemId, $params)
    {
        $year = $params['year'] ?? date('Y');
        $month = $params['month'] ?? date('m');

        $query = "
            WITH days AS (
                SELECT generate_series(
                    '$year-$month-01'::DATE, 
                    '$year-$month-01'::DATE + INTERVAL '1 month' - INTERVAL '1 day', 
                    '1 day'::INTERVAL
                )::DATE AS day
            ),
            item_prices AS (
                SELECT
                    i.id AS item_id,
                    i.name AS item_name,
                    i.price,
                    i.created_at::DATE AS created_day
                FROM items i
                WHERE i.id = :item_id
                  AND EXTRACT(YEAR FROM i.created_at) <= :year
                  AND EXTRACT(MONTH FROM i.created_at) <= :month
            ),
            item_stock_prices AS (
                SELECT
                    i.id AS item_id,
                    i.name AS item_name,
                    i.price,
                    is_stock.created_at::DATE AS created_day
                FROM items i
                JOIN item_stocks is_stock ON i.id = is_stock.item_id
                WHERE i.id = :item_id
                  AND EXTRACT(YEAR FROM is_stock.created_at) <= :year
                  AND EXTRACT(MONTH FROM is_stock.created_at) <= :month
            ),
            combined_prices AS (
                SELECT 
                    created_day,
                    item_id,
                    item_name,
                    price,
                    ROW_NUMBER() OVER (PARTITION BY created_day ORDER BY created_day DESC) AS row_num
                FROM (
                    SELECT created_day, item_id, item_name, price FROM item_prices
                    UNION ALL
                    SELECT created_day, item_id, item_name, price FROM item_stock_prices
                ) cp
            ),
            latest_prices AS (
                SELECT
                    created_day,
                    item_id,
                    item_name,
                    price
                FROM combined_prices
                WHERE row_num = 1
            ),
            filled_prices AS (
                SELECT 
                    d.day,
                    lp.item_id,
                    lp.item_name,
                    lp.price,
                    -- Propagate the last known price forward
                    CASE
                        WHEN lp.price IS NOT NULL THEN lp.price
                        ELSE COALESCE(prev_price.price, prev_month_price.price)
                    END AS filled_price
                FROM days d
                LEFT JOIN latest_prices lp ON d.day = lp.created_day
                LEFT JOIN LATERAL (
                    SELECT price
                    FROM latest_prices 
                    WHERE created_day <= d.day AND item_id = :item_id
                    ORDER BY created_day DESC
                    LIMIT 1
                ) prev_price ON true
                LEFT JOIN LATERAL (
                    SELECT price
                    FROM latest_prices 
                    WHERE created_day < '$year-$month-01'::DATE AND item_id = :item_id
                    ORDER BY created_day DESC
                    LIMIT 1
                ) prev_month_price ON d.day = '$year-$month-01'::DATE
            )
            SELECT 
                EXTRACT(DAY FROM day)::INT AS day,
                COALESCE(TO_CHAR(filled_price, 'FM999999990.00'), '0.00') AS price,
                TO_CHAR(day, 'YYYY-MM-DD') AS date
            FROM filled_prices
            ORDER BY day;
            ";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':year', $year, \PDO::PARAM_INT);
        $stmt->bindParam(':month', $month, \PDO::PARAM_INT);
        $stmt->bindParam(':item_id', $itemId, \PDO::PARAM_STR);

        try {
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $result;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

}
