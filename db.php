<?php

class Database {
    private $host = "db";
    private $db_name = "inventory_db";
    private $username = "root";
    private $password = "rootpassword";
    private $conn;


public function getConnection() {
    $this->conn = null;

    try {
        $this->conn = new PDO("mysql:host=". $this->host. ";dbname=". $this->db_name, $this->username, $this->password);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $exception) {
        echo "Connection failed: ". $exception->getMessage();
    }
    return $this->conn;
}

}