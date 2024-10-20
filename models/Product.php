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
        $query = "SELECT * FROM $this->table WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function fetchProducts($page, $pageSize)
    {
        $offset = ($page - 1) * $pageSize;

        $total = $this->countProduct();

        $query = "
		SELECT 
			p.id,
			p.code,
			p.name,
			p.price,
			p.low_stock_alert,
			i.on_hand || ' ' || u.name || CASE WHEN i.on_hand > 1 THEN 's' ELSE '' END AS on_hand,
			i.warehouse_id,
			p.media
		FROM $this->table p
		LEFT JOIN inventory i ON p.id = i.product_id
		LEFT JOIN units u ON p.unit_id = u.id
		LIMIT :pageSize OFFSET :offset
	";

        $stmt = $this->db->prepare($query);
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

    public function getTotalUnitCount($filter = [])
    {
        $query = "
            SELECT 
                SUM(i.on_hand) AS total_items,
                COUNT(CASE WHEN p.low_stock_alert = true THEN 1 END) AS low_stock_alerts,
                SUM(i.to_be_delivered) AS total_to_be_delivered,
                SUM(i.to_be_ordered) AS total_to_be_ordered
            FROM $this->table p
            LEFT JOIN inventory i ON p.id = i.product_id
            LEFT JOIN units u ON p.unit_id = u.id
            ";

        $conditions = [];
        $bindings = [];

        // Low Stock Filter (Only affects low_stock_alerts count)
        if (isset($filter['low_stock']) && $filter['low_stock'] === true) {
            $conditions[] = "p.low_stock_alert = true";
        }

        // Warehouse ID Filter
        if (isset($filter['warehouse_id'])) {
            $conditions[] = "i.warehouse_id = :warehouse_id";
            $bindings[':warehouse_id'] = $filter['warehouse_id'];
        }

        // Optional Unit ID Filter
        if (isset($filter['unit_id'])) {
            $conditions[] = "u.id = :unit_id";
            $bindings[':unit_id'] = $filter['unit_id'];
        }

        // Apply Conditions
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $countStmt = $this->db->prepare($query);

        // Bind Values
        foreach ($bindings as $key => $value) {
            $countStmt->bindValue($key, $value);
        }

        $countStmt->execute();

        // Return results directly
        return $countStmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function countProduct($filter = [])
    {
        $query = "
            SELECT COUNT(*) AS total 
            FROM $this->table p
            LEFT JOIN units u ON p.unit_id = u.id
            ";

        $conditions = [];
        $bindings = [];

        // Optional Unit ID Filter
        if (isset($filter['unit_id'])) {
            $conditions[] = "u.id = :unit_id";
            $bindings[':unit_id'] = $filter['unit_id'];
        }

        // Apply Conditions
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $countStmt = $this->db->prepare($query);

        // Bind Values
        foreach ($bindings as $key => $value) {
            $countStmt->bindValue($key, $value);
        }

        $countStmt->execute();

        // Return the count
        return $countStmt->fetchColumn();
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
