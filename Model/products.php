<?php 

require_once __DIR__ ."/../db.php";

class Product {
    private $conn;
    private $table = 'products';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    //Create a new Product

    public function create($name, $location, $vendor, $code, $price, $profit, $margin, $quantity, $unit, $image_path) {
        $query = "INSERT INTO " . $this->table . "(name, location, vendor, code, price, profit, margin, quantity, unit, image_path) VALUES (:name, :location, :vendor, :code, :price, :profit, :margin, :quantity, :unit, :image_path)";
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
        return $stmt->execute();
    }

   

      // Get All Products
      public function getAll() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Get Single Product
    public function get($id) {
        $query = "SELECT * FROM $this->table WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update a Product
    public function update($id, $name, $location, $vendor, $code, $price, $profit, $margin, $quantity, $unit, $image_path) {
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
      public function delete($id) {
        $query = "DELETE FROM $this->table WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }





















}