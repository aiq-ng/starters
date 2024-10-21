<?php

namespace Models;

use Database\Database;

class Inventory
{
    private $db;

    private $table = 'inventory';

    private $table_plans ='inventory_plans';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();

    }


    // Add a product to a inventory
    public function addInventory($data)
    {
        $query = "INSERT INTO " . $this->table . "
        (product_id, warehouse_id, storage_id, quantity, on_hand, to_be_delivered, to_be_ordered) 
        VALUES (:product_id, :warehouse_id, :storage_id, :quantity, :on_hand, :to_be_delivered, :to_be_ordered)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':product_id', $data['product_id']);
        $stmt->bindParam(':warehouse_id', $data['warehouse_id']);
        $stmt->bindParam(':storage_id', $data['storage_id']);
        $stmt->bindParam(':quantity', $data['quantity']);
        $stmt->bindParam('on_hand', $data['on_hand']);
        $stmt->bindParam(':to_be_delivered', $data['to_be_delivered']);
        $stmt->bindParam(':to_be_ordered', $data['to_be_ordered']);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    // Get the stock level for a warehouse
    public function getInventoryByWarehouse($warehouse_id)
    {
        $query = "SELECT i.*, p.name as product_name, w.name as warehouse_name 
        FROM " . $this->table . " i
        JOIN products p ON i.product_id = p.id
        JOIN warehouses w ON i.warehouse_id = w.id
        WHERE i.warehouse_id = :warehouse_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':warehouse_id', $warehouse_id);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Update product stock in warehouse
    public function updateStock($warehouse_id, $product_id, $quantity, $progress)
    {
        $query = "UPDATE " . $this->table . "
        SET quantity = :quantity, progress = :progress
        WHERE warehouse_id = :warehouse_id AND product_id = :product_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':warehouse_id', $warehouse_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':progress', $progress);

        return $stmt->execute();
    }

    //Create a new inventory plan
    public function createInventoryPlan ($data) {

        $query = "INSERT INTO" . $this->table_plans . "(name, inventory_date, warehouse_id, status, progress) VALUES (:name, :inventory_date, :warehouse_id, :status, :progress)";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':inventory_date', $data['inventory_date']);
        $stmt->bindParam(':warehouse_id', $data['warehouse_id']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':progress', $data['progress']);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    
    //Get all inventory plans

    public function getAllInventoryPlans() {
        $query = "SELECT ip.*, w.name as warehouse_name FROM " . $this->table_plans . " ip JOIN warehouses w ON ip.warehouse_id = w.id";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Get a single inventory plan by id
    public function getInventoryPlan($id)
    {
        $query = "SELECT * FROM " . $this->table_plans . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Update an inventory plan
    public function update($id, $data)
    {
        $query = "UPDATE " . $this->table_plans . "
        SET name = :name, status = :status, inventory_date = :inventory_date, warehouse_id = :warehouse_id, progress = :progress 
        WHERE id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':inventory_date', $data['inventory_date']);
        $stmt->bindParam(':warehouse_id', $data['warehouse_id']);
        $stmt->bindParam(':progress', $data['progress']);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

}






    

