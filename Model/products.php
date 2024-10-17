<?php 

require_once __DIR__ ."/../db.php";

class Product {
    private $conn;
    private $table = 'products';
    private $warehouse = 'warehouse';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    //Create a new Product

    public function create($name, $location, $vendor, $code, $sku, $barcode, $price, $quantity, $unit, $media_path) {
        $query = "INSERT INTO " . $this->table . "(name, location, vendor, code, sku, barcode, price, quantity, unit, media_path) VALUES (:name, :location, :vendor, :code, :sku, :barcode, :price, :quantity, :unit, :media_path)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':vendor', $vendor);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':sku', $sku);
        $stmt->bindParam(':barcode', $barcode);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':unit', $unit);
        $stmt->bindParam(':media_path', $media_path);
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

    // Return the total number of items

    public function getTotalItems(){
        $query = "SELECT COUNT(*) FROM $this->table";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);  
    }

    // Return total number of low stock alerts 

    public function getLowStockAlerts(){
        $query = "SELECT COUNT(*) FROM $this->table WHERE quantity <= 50";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //Classify based on warehouses, warehouse A = Cold room, warehouse B = Kitchen

    //Display the number of products in both warehouse 
    public function getWhNo(){
        $query = "SELECT location, COUNT(*) AS product_count FROM $this->table WHERE location IN ('Warehouse A', 'Warehouse B') GROUP BY location ORDER BY location";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    //Display the number of items in both warehouses
    public function getWhItems(){
        $query = "SELECT location, SUM(quantity) AS quantity_count FROM $this->table WHERE location IN ('Warehouse A', 'Warehouse B') GROUP BY location ORDER BY location";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    //Display all the products in warehouse A
    public function getWhA(){
        $query = "SELECT *, COUNT(*) AS product_count FROM $this->table WHERE location = 'Warehouse A'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }




    
   














}