<?php 

require_once __DIR__ ."/../db.php";

class Wh {
    private $conn;

    private $warehouse = 'warehouses';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    
    //Create Warehouse and Store Address

    public function createWh($name, $address) {
        $query = "INSERT INTO " . $this->warehouse . "(name, address) VALUES (:name, :address)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':address', $address);
        return $stmt->execute();
    }

    //Get Warehouse information
    public function getWh($name) {
        $query = 'SELECT * FROM $this->warehouse WHERE name = :name';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }

}