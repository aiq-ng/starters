<?php

namespace Models;

use Database\Database;

class Purchase
{
    private $db;
    private $table = 'purchase';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

}
