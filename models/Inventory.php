<?php

namespace Models;

use Database\Database;
use Services\Utils;

class Inventory
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();

    }

    private function getItemCurrentQuantity($itemId)
    {
        $sql = "
            SELECT COALESCE(SUM(quantity), 0) AS total_quantity
            FROM item_stocks
            WHERE item_id = :itemId
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':itemId', $itemId);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function getInventory($filter = null)
    {
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
                i.availability, 
                i.media
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

        if (!empty($filter['availability'])) {
            $stmt->bindValue(':filterAvailability', $params['filterAvailability'], \PDO::PARAM_STR);
        }

        if (!empty($search)) {
            $stmt->bindValue(':search', $params['search'], \PDO::PARAM_STR);
        }

        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($results as &$item) {
            $item['media'] = !empty($item['media']) ? json_decode($item['media'], true) : null;
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
    }

    private function countInventory($filter = null)
    {
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
                MAX(its.expiry_date) AS expiry_date,
                COALESCE(SUM(its.quantity), 0) AS remaining_stock
            FROM item_stocks its
            JOIN items i ON its.item_id = i.id
            LEFT JOIN item_stock_departments isd ON its.id = isd.stock_id
            LEFT JOIN item_stock_vendors isv ON its.id = isv.stock_id
            LEFT JOIN item_stock_manufacturers ism ON its.id = ism.stock_id
            LEFT JOIN item_stock_branches isb ON its.id = isb.stock_id
            LEFT JOIN departments d ON isd.department_id = d.id
            LEFT JOIN item_categories ic ON i.category_id = ic.id
            WHERE its.item_id = :itemId
            GROUP BY i.id, i.name, i.threshold_value, i.opening_stock, i.media,
                isd.department_id, isv.vendor_id, ism.manufacturer_id, isb.branch_id
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

        $stmt->bindParam(':itemId', $itemId);

        return $stmt->execute();
    }


    public function createItem($data, $mediaLinks = [])
    {
        try {
            $this->db->beginTransaction();

            $sql = "
                INSERT INTO items
                (name, description, unit_id, category_id,
                price, threshold_value, media, opening_stock)
                VALUES (:name, :description, :unitId, :categoryId,
                :price, :threshold, :media, :openingStock)
                RETURNING id
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

            if (!$stmt->execute()) {
                throw new \Exception('Failed to insert item.');
            }

            $itemId = $itemId = $stmt->fetchColumn();

            if (!$this->createItemStock($itemId, $data)) {
                throw new \Exception('Failed to insert item stock.');
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

    public function createItemStock($itemId, $data)
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

            return $stockId;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function updateItem($itemId, $data, $mediaLinks = [])
    {
        try {
            $sql = "
                UPDATE items
                SET name = :name, description = :description, unit_id = :unitId,
                    category_id = :categoryId, price = :price,
                    threshold_value = :thresholdValue, media = :media,
                    opening_stock = :openingStock 
                WHERE id = :itemId
            ";

            $mediaLinks = json_encode($mediaLinks);
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':itemId', $itemId);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':unitId', $data['unit_id']);
            $stmt->bindParam(':categoryId', $data['category_id']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':thresholdValue', $data['threshold_value']);
            $stmt->bindParam(':media', $mediaLinks);
            $stmt->bindParam(':openingStock', $data['quantity']);

            if (!$stmt->execute()) {
                throw new \Exception('Failed to update item.');
            }

            $current_quantity = $this->getItemCurrentQuantity($itemId);
            $difference = $data['quantity'] - $current_quantity;

            if ($difference < 0) {
                error_log("Subtracting stock");
                $stockIds = $this->adjustStock($itemId, [
                    'quantity' => abs($difference),
                    'adjustment_type' => 'subtraction',
                    'description' => 'Edit item',
                    'manager_id' => $data['manager_id'],
                    'source_id' => $data['source_id'],
                    'source_department_id' => $data['source_department_id'],
                    'source_type' => 'user',
                ]);
            } elseif ($difference > 0) {
                error_log("Adding stock");
                $stockIds = $this->adjustStock($itemId, [
                    'quantity' => $difference,
                    'adjustment_type' => 'addition',
                    'description' => 'Edit item',
                    'manager_id' => $data['manager_id'],
                    'source_id' => $data['source_id'],
                    'source_department_id' => $data['source_department_id'],
                    'source_type' => 'vendor',
                ]);
            } else {
                error_log("No stock change required");
            }

            if ($difference !== 0) {
                if (!$this->upsertItemRelationships($stockIds, $data)) {
                    throw new \Exception('Failed to update or insert item relationships.');
                }
            }

            return true;

        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
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
    }

    private function upsertItemStockDepartment($stockId, $departmentId)
    {
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
    }

    private function upsertItemStockManufacturer($stockId, $manufacturerId)
    {
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
    }

    private function upsertItemStockBranch($stockId, $branchId)
    {
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

                $query = "
                    INSERT INTO item_stock_adjustments
                    (stock_id, quantity, adjustment_type,
                    description, source_id, source_department_id, manager_id, source_type)
                    VALUES (:stockId, :quantity, :adjustmentType,
                    :description, :source_id, :source_department_id, :manager_id, 'vendor')
                ";
                $stmt = $this->db->prepare($query);

                $stmt->bindValue(':stockId', $newStockId);
                $stmt->bindValue(':quantity', $quantity);
                $stmt->bindValue(':adjustmentType', $data['adjustment_type']);
                $stmt->bindValue(':description', $data['description']);
                $stmt->bindValue(':source_id', $data['source_id']);
                $stmt->bindValue(':source_department_id', $data['source_department_id']);
                $stmt->bindValue(':manager_id', $data['manager_id']);

                if (!$stmt->execute()) {
                    throw new \Exception('Failed to log stock adjustment for addition.');
                }

                $affectedStockIds[] = $newStockId;
            }

            $this->db->commit();
            return $affectedStockIds;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            throw $e;
        }
    }

    private function subtractStockFIFO($itemId, $quantity, $data)
    {
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

                    $logSql = "
                        INSERT INTO item_stock_adjustments
                        (stock_id, quantity, adjustment_type,
                        description, source_id, source_department_id, manager_id, source_type)
                        VALUES (:stockId, :quantity, :adjustmentType,
                        :description, :source_id, :source_department_id, :manager_id, 'user')
                    ";
                    $logStmt = $this->db->prepare($logSql);
                    $logStmt->bindValue(':stockId', $stockId);
                    $logStmt->bindValue(':quantity', $subtractAmount);
                    $logStmt->bindValue(':adjustmentType', $data['adjustment_type']);
                    $logStmt->bindValue(':description', $data['description']);
                    $logStmt->bindValue(':source_id', $data['source_id']);
                    $logStmt->bindValue(':source_department_id', $data['source_department_id']);
                    $logStmt->bindValue(':manager_id', $data['manager_id']);

                    if (!$logStmt->execute()) {
                        throw new \Exception('Failed to log stock adjustment.');
                    }
                }
            }
        }

        if ($quantity > 0) {
            throw new \Exception('Insufficient stock to complete the operation.');
        }

        return $affectedStockIds;
    }

    private function getStocksByItemId($itemId)
    {
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
    }


    public function getAdjustmentHistory($itemId)
    {
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
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function commentOnItemHistory($itemStockId, $data)
    {
        $sql = "
            INSERT INTO comments
            (entity_id, entity_type, user_id, parent_id, comment)
            VALUES (:entityId, :entityType, :userId, :parentId, :comment)
            RETURNING id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':entityId', $itemStockId);
        $stmt->bindValue(':entityType', 'item_stock_adjustment');
        $stmt->bindValue(':userId', $data['user_id'] ?? null);
        $stmt->bindValue(':parentId', $data['parent_id'] ?? null);
        $stmt->bindValue(':comment', $data['comment'] ?? null);

        $stmt->execute();

        return $stmt->fetchColumn();
    }
}
