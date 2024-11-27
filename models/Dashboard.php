<?php

namespace Models;

use Database\Database;

class Dashboard
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getOverview()
    {
        $query = "
        SELECT 
            (SELECT COUNT(*) FROM item_categories) AS categories,
            (SELECT COUNT(*) FROM items) AS total_products,
            (SELECT COUNT(*) FROM vendors) AS vendors,
            (SELECT COUNT(*) FROM items WHERE availability = 'low stock') AS low_stocks
        ";

        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return [
                'categories' => 0,
                'total_products' => 0,
                'vendors' => 0,
                'low_stocks' => 0,
            ];
        }
    }

    public function getLowQuantityStock()
    {
        $query = "
			SELECT 
				i.id, 
				i.name, 
				i.media,
				CONCAT(i.quantity, ' ', u.name) AS remaining_quantity 
			FROM items i
			LEFT JOIN units u ON i.unit_id = u.id
			WHERE i.quantity < i.threshold_value
		";

        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as &$row) {
                $row['media'] = $row['media'] ? json_decode($row['media'], true) : null;
            }

            return $result;
        } catch (\PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return [];
        }
    }
}
