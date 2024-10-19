<?php

namespace Models;

use Database\Database;

class Inventory
{
    private $db;

    private $table = 'inventory';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();

    }

    // public function {}


}
