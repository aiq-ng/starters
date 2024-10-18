<?php

require_once __DIR__ ."/../db.php";


class Inventory {

    private $conn;
    
    private $table = 'inventory';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // public function {}


}
