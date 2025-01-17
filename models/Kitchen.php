<?php

namespace Models;

use Database\Database;

class Kitchen
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();

    }

    public function markAsPrepared(array $ids)
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $query = "UPDATE sales_orders SET status = 'prepared' WHERE id IN ($placeholders)";
        $stmt = $this->db->prepare($query);

        $stmt->execute($ids);
    }

}
