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
    public function create($data)
    {
        $this->db->beginTransaction();

        try {
            $query = "
            INSERT INTO " . $this->table . "
            (name, code, sku, barcode, price, unit_id, media)
            VALUES 
            (:name, :code, :sku, :barcode, :price, :unit_id, :media)";

            $stmt = $this->db->prepare($query);

            $params = [
                ':name' => $data['name'],
                ':code' => $data['code'],
                ':sku' => $data['sku'],
                ':barcode' => $data['barcode'],
                ':price' => $data['price'],
                ':unit_id' => $data['unit'],
                ':media' => json_encode($data['media']),
            ];

            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if (!$stmt->execute()) {
                throw new \Exception('Failed to create product.');
            }

            $productId = $this->db->lastInsertId();
            $this->createProductVendorRelationship($productId, $data['vendor']);
            $this->createInventoryRelationship($productId, $data);

            $this->db->commit();

            return $productId;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('Error: ' . $e->getMessage());
            return null;
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
        // Debugging: Check the structure of $data
        error_log('Data received: ' . print_r($data, true));

        // Ensure $data is an array
        if (!is_array($data)) {
            throw new \Exception('Invalid data provided. Expected an array.');
        }
        $query = "
            INSERT INTO inventory (product_id, warehouse_id, quantity, on_hand, storage_id)
            VALUES (:product_id, :warehouse_id, :quantity, :on_hand, :storage_id)
            ON CONFLICT (product_id, warehouse_id, storage_id) DO NOTHING";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':product_id', $productId);
        $stmt->bindValue(':warehouse_id', $data['location']);
        $stmt->bindValue(':quantity', $data['quantity']);
        $stmt->bindValue(':on_hand', $data['quantity']);
        $stmt->bindValue(':storage_id', $data['storage_id'] ?? 1);

        if (!$stmt->execute()) {
            throw new \Exception('Failed to create inventory relationship.');
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
