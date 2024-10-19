<?php

require_once __DIR__ ."/../db.php";


class Inventory
{
    private $conn;

    private $table = 'inventory';

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();

    }

    // public function {}


}
