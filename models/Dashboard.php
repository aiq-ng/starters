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

    public function getMostPurchasedItems($filters = [])
    {
        $page = $filters['page'] ?? 1;
        $pageSize = $filters['page_size'] ?? 5;
        $offset = ($page - 1) * $pageSize;

        $query = "
            SELECT 
                i.name AS item_name,
                SUM(poi.quantity) AS purchased_quantity,
                i.quantity - SUM(poi.quantity) AS remaining_quantity,
                SUM(poi.quantity * poi.price) AS total_amount
            FROM
                purchase_order_items poi
            JOIN
                items i
            ON poi.item_id = i.id
        ";

        $conditions = [];
        $params = [];

        if (!empty($filters['month'])) {
            $conditions[] = "DATE_PART('month', poi.created_at) = :month";
            $params['month'] = (int) $filters['month'];
        }

        if (!empty($filters['year'])) {
            $conditions[] = "DATE_PART('year', poi.created_at) = :year";
            $params['year'] = (int) $filters['year'];
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= "
        GROUP BY i.id, i.name, i.quantity
        ORDER BY purchased_quantity DESC
        LIMIT :pageSize OFFSET :offset
    ";

        $params['pageSize'] = $pageSize;
        $params['offset'] = $offset;

        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $totalItems = $this->getTotalItemCount($filters);

        $meta = [
            'current_page' => (int) $page,
            'page_size' => (int) $pageSize,
            'total_data' => (int) $totalItems,
            'total_pages' => ceil($totalItems / $pageSize),
        ];

        return [
            'data' => $data,
            'meta' => $meta,
        ];
    }

    private function getTotalItemCount($filters = [])
    {
        $countQuery = "
            SELECT COUNT(DISTINCT i.id) AS total_items
            FROM purchase_order_items poi
            JOIN items i
            ON poi.item_id = i.id
        ";

        $conditions = [];
        $params = [];

        if (!empty($filters['month'])) {
            $conditions[] = "DATE_PART('month', poi.created_at) = :month";
            $params['month'] = (int) $filters['month'];
        }

        if (!empty($filters['year'])) {
            $conditions[] = "DATE_PART('year', poi.created_at) = :year";
            $params['year'] = (int) $filters['year'];
        }

        if (!empty($conditions)) {
            $countQuery .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $this->db->prepare($countQuery);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}
