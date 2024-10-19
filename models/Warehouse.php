<?php

namespace Models;

use Database\Database;

class Warehouse
{
    private $db;

    private $warehouse = 'warehouses';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }


    //Create Warehouse and Store Address

    public function createWh($name, $address)
    {
        $query = "INSERT INTO " . $this->warehouse . "(name, address) VALUES (:name, :address)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':address', $address);
        return $stmt->execute();
    }

    //Get Warehouse information
    public function getWh($name)
    {
        $query = 'SELECT * FROM $this->warehouse WHERE name = :name';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

}
