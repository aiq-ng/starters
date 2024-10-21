<?php

namespace Models;

use Database\Database;

class Product
{
    private $db;
    private $table = 'products';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }


    // Create a new Product
    public function create($data, $files = [])
    {
        $this->db->beginTransaction();

        try {
            $query = "
                INSERT INTO " . $this->table . "
                (name, code, sku, barcode, price, unit_id, media)
                VALUES 
                (:name, :code, :sku, :barcode, :price, :unit_id, :media)";

            $stmt = $this->db->prepare($query);

            $stmt->bindValue(':name', $data['name']);
            $stmt->bindValue(':code', $data['code']);
            $stmt->bindValue(':sku', $data['sku']);
            $stmt->bindValue(':barcode', $data['barcode']);
            $stmt->bindValue(':price', $data['price']);
            $stmt->bindValue(':unit_id', $data['unit']);
            $stmt->bindValue(':media', json_encode($files) ?? null);

            if (!$stmt->execute()) {
                throw new \Exception('Failed to create product.');
            }

            $productId = $this->db->lastInsertId();
            $this->createProductVendorRelationship($productId, $data['vendor']);
            $this->createInventoryRelationship($productId, $data);
            $this->db->commit();

            return $productId;

        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function createProductVendorRelationship($productId, $vendorId)
    {
        $query = "
            INSERT INTO product_vendors (product_id, vendor_id)
            VALUES (:product_id, :vendor_id)
            ON CONFLICT (product_id, vendor_id) DO NOTHING";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':product_id', $productId);
        $stmt->bindValue(':vendor_id', $vendorId);

        if (!$stmt->execute()) {
            throw new \Exception('Failed to create product-vendor relationship.');
        }
    }

    private function createInventoryRelationship($productId, $data)
    {
        $query = "
            INSERT INTO inventory (product_id, warehouse_id, quantity, on_hand, storage_id)
            VALUES (:product_id, :warehouse_id, :quantity, :on_hand, :storage_id)
            ON CONFLICT (product_id, warehouse_id, storage_id) DO NOTHING";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':product_id', $productId);
        $stmt->bindValue(':warehouse_id', $data['location']);
        $stmt->bindValue(':quantity', $data['quantity']);
        $stmt->bindValue(':on_hand', $data['quantity']);
        $stmt->bindValue(':storage_id', $data['storage'] ?? 1);

        if (!$stmt->execute()) {
            throw new \Exception('Failed to create inventory relationship.');
        }
    }

    public function fetchProduct($id)
    {
        $query = "
		SELECT
			p.media, 
			COALESCE(SUM(i.on_hand), 0) AS on_hand, 
			COALESCE(SUM(i.to_be_delivered), 0) AS to_be_delivered, 
			COALESCE(SUM(i.to_be_ordered), 0) AS to_be_ordered,
			p.name, 
			w.name AS location, 
			v.name AS top_vendor,
			p.code,
			p.sku,
			p.barcode,
			p.price,
			p.profit,
			p.margin,
			COALESCE(SUM(i.quantity), 0) AS quantity,
			u.name AS unit
		FROM $this->table p
		LEFT JOIN inventory i ON p.id = i.product_id
		LEFT JOIN product_vendors pv ON p.id = pv.product_id
		LEFT JOIN vendors v ON pv.vendor_id = v.id
		LEFT JOIN warehouses w ON i.warehouse_id = w.id
		LEFT JOIN units u ON p.unit_id = u.id
		WHERE p.id = :id
		GROUP BY p.id, p.media, p.name, w.name, v.name, u.name, p.code, p.sku, p.barcode, p.price, p.profit, p.margin
		ORDER BY on_hand DESC
		LIMIT 1
	";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $product = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($product && isset($product['media'])) {
            $product['media'] = json_decode($product['media'], true);
        }

        return $product;
    }

    public function fetchProducts($page, $pageSize, $withTotalSold = false, $filter = [])
    {
        $offset = ($page - 1) * $pageSize;

        $total = $this->countProducts($filter)['total_products'];

        $query = "SELECT p.id, p.name, p.media";

        // Modify query to include total items sold if needed
        if ($withTotalSold) {
            $query .= ", COALESCE(SUM(s.quantity), 0) AS total_items_sold";
        } else {
            $query .= ",
            p.code, 
            p.price, 
            p.low_stock_alert, 
            i.on_hand || ' ' || u.name || CASE WHEN i.on_hand > 1 THEN 's' ELSE '' END AS on_hand, 
            i.warehouse_id
        ";
        }

        $query .= " FROM $this->table p";

        // Common JOINs
        if (!$withTotalSold) {
            $query .= "
            LEFT JOIN inventory i ON p.id = i.product_id
            LEFT JOIN units u ON p.unit_id = u.id
        ";
        }

        // Add sales JOIN if `total_items_sold` is required
        if ($withTotalSold) {
            $query .= " LEFT JOIN sales s ON p.id = s.product_id";
        }

        $conditions = [];
        $bindings = [];

        if (isset($filter['unit_id'])) {
            $conditions[] = "p.unit_id = :unit_id";
            $bindings[':unit_id'] = $filter['unit_id'];
        }

        if (isset($filter['warehouse_id'])) {
            $conditions[] = "i.warehouse_id = :warehouse_id";
            $bindings[':warehouse_id'] = $filter['warehouse_id'];
        }

        if (isset($filter['search']) && !empty($filter['search'])) {
            $searchTerm = $filter['search'];
            $conditions[] = "(p.name ILIKE :search OR p.code ILIKE :search)";
            $bindings[':search'] = "%$searchTerm%";
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        // Group by and Order if fetching total items sold
        if ($withTotalSold) {
            $query .= " GROUP BY p.id, p.name, p.media";
            $query .= " ORDER BY total_items_sold DESC";
        }

        $query .= " LIMIT :pageSize OFFSET :offset";

        $stmt = $this->db->prepare($query);

        foreach ($bindings as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':pageSize', $pageSize, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($products as &$product) {
            if (!empty($product['media'])) {
                $product['media'] = json_decode($product['media'], true);
            }
        }

        $meta = [
            'current_page' => $page,
            'last_page' => ceil($total / $pageSize),
            'total' => $total
        ];

        return [
            'products' => $products,
            'meta' => $meta
        ];
    }


    public function countProducts($filter = [])
    {
        $query = "
            SELECT COUNT(*) AS total_products, 
                   COUNT(u.id) AS total_units
            FROM $this->table p
            LEFT JOIN units u ON p.unit_id = u.id
        ";

        $conditions = [];
        $bindings = [];

        if (isset($filter['unit_id'])) {
            $conditions[] = "u.id = :unit_id";
            $bindings[':unit_id'] = $filter['unit_id'];
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $countStmt = $this->db->prepare($query);

        foreach ($bindings as $key => $value) {
            $countStmt->bindValue($key, $value);
        }

        $countStmt->execute();

        return $countStmt->fetch(\PDO::FETCH_ASSOC);
    }


    public function countUnits($filter = [])
    {
        $query = "SELECT";
        $selects = [];
        $conditions = [];
        $bindings = [];

        if (isset($filter['low_stock']) && $filter['low_stock'] === true) {
            $selects[] = "COUNT(CASE WHEN p.low_stock_alert = true THEN 1 END)";
            $conditions[] = "p.low_stock_alert = true";
        }

        if (isset($filter['to_be_delivered']) && $filter['to_be_delivered'] === true) {
            $selects[] = "SUM(i.to_be_delivered)";
            $conditions[] = "i.to_be_delivered > 0";
        }

        if (isset($filter['to_be_ordered']) && $filter['to_be_ordered'] === true) {
            $selects[] = "SUM(i.to_be_ordered)";
            $conditions[] = "i.to_be_ordered > 0";
        }

        // Default selection if no specific filter is provided
        if (empty($selects)) {
            $selects[] = "SUM(i.on_hand)";
        }

        $query .= " " . implode(", ", $selects) . "
		FROM $this->table p
		LEFT JOIN inventory i ON p.id = i.product_id
		LEFT JOIN units u ON p.unit_id = u.id";

        // Apply additional filters
        if (isset($filter['unit_id'])) {
            $conditions[] = "u.id = :unit_id";
            $bindings[':unit_id'] = $filter['unit_id'];
        }

        // Storage ID filter
        if (isset($filter['storage_id'])) {
            $conditions[] = "i.storage_id = :storage_id";
            $bindings[':storage_id'] = $filter['storage_id'];
        }

        // Apply conditions if any
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $countStmt = $this->db->prepare($query);

        // Bind values
        foreach ($bindings as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();

        return $countStmt->fetchColumn();
    }


    public function updateProductQuantity($productId, $data)
    {
        try {
            $this->db->beginTransaction();

            $currentQuantityQuery = "
            SELECT quantity 
            FROM inventory 
            WHERE product_id = :product_id
        ";
            $currentStmt = $this->db->prepare($currentQuantityQuery);
            $currentStmt->bindParam(':product_id', $productId, \PDO::PARAM_INT);
            $currentStmt->execute();
            $currentQuantity = $currentStmt->fetchColumn();

            if ($currentQuantity === false) {
                return [
                    "message" => "Product not found in inventory."
                ];
            }

            $discrepancy = $data['new_quantity'] - $currentQuantity;

            $updateQuery = "
            UPDATE inventory
            SET quantity = :new_quantity
            WHERE product_id = :product_id
        ";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':new_quantity', $data['new_quantity'], \PDO::PARAM_INT);
            $updateStmt->bindParam(':product_id', $productId, \PDO::PARAM_INT);
            $updateStmt->execute();

            $auditQuery = "
            INSERT INTO inventory_audits 
            (product_id, user_id, old_quantity, new_quantity, discrepancy, reason, notes, updated_at)
            VALUES (:product_id, :user_id, :old_quantity, :new_quantity, :discrepancy, :reason, :notes, NOW())
        ";
            $auditStmt = $this->db->prepare($auditQuery);
            $auditStmt->bindParam(':product_id', $productId, \PDO::PARAM_INT);
            $auditStmt->bindParam(':user_id', $data['user_id'], \PDO::PARAM_INT);
            $auditStmt->bindParam(':old_quantity', $currentQuantity, \PDO::PARAM_INT);
            $auditStmt->bindParam(':new_quantity', $data['new_quantity'], \PDO::PARAM_INT);
            $auditStmt->bindParam(':discrepancy', $discrepancy, \PDO::PARAM_INT);
            $auditStmt->bindParam(':reason', $data['reason'], \PDO::PARAM_STR);
            $auditStmt->bindParam(':notes', $data['notes'], \PDO::PARAM_STR);
            $auditStmt->execute();

            // Commit transaction if all operations are successful
            $this->db->commit();

            return [
                "current_quantity" => $currentQuantity,
                "new_quantity" => $data['new_quantity'],
                "discrepancy" => $discrepancy,
                "reason" => $data['reason'],
                "notes" => $data['notes'],
                "user_id" => $data['user_id'],
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }


    // Get All Products
    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    // Get Single Product
    public function get($id)
    {
        $query = "SELECT * FROM $this->table WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Update a Product
    public function update($id, $name, $location, $vendor, $code, $price, $profit, $margin, $quantity, $unit, $image_path)
    {
        $query = "UPDATE ". $this->table. " SET name = :name, location = :location, vendor = :vendor, code = :code, price = :price, profit = :profit, margin = :margin, quantity = :quantity, unit = :unit, image_path = :image_path WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':vendor', $vendor);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':profit', $profit);
        $stmt->bindParam(':margin', $margin);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':unit', $unit);
        $stmt->bindParam(':image_path', $image_path);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Delete Product
    public function delete($id)
    {
        $query = "DELETE FROM $this->table WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Return the total number of items

    public function getTotalItems()
    {
        $query = "SELECT COUNT(*) as item_count FROM $this->table";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Return total number of low stock alerts

    public function getLowStockAlerts()
    {
        $query = "SELECT COUNT(*) as low_count_alert FROM $this->table WHERE quantity <= 50";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    //Classify based on warehouses, warehouse A = Cold room, warehouse B = Kitchen

    //Display the number of products in both warehouse
    public function getWhNo()
    {
        $query = "SELECT location, COUNT(*) AS product_count FROM $this->table WHERE location IN ('Warehouse A', 'Warehouse B') GROUP BY location ORDER BY location";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    //Display the number of items in both warehouses
    public function getWhItems()
    {
        $query = "SELECT location, SUM(quantity) AS quantity_count FROM $this->table WHERE location IN ('Warehouse A', 'Warehouse B') GROUP BY location ORDER BY location";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    //Display the  the products in warehouse A
    public function getWhA()
    {
        $query = "SELECT * FROM $this->table WHERE location = 'Warehouse A'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    //Display the  the products in warehouse B

    public function getWhB()
    {
        $query = "SELECT * FROM $this->table WHERE location = 'Warehouse B'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    //Display low stock alerts in Warehouse A
    public function getLowStockAlertsA()
    {
        $query = "SELECT * FROM $this->table WHERE quantity <= 50 location = 'Warehouse A'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }



















}
