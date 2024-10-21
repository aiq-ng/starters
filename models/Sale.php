<?php

namespace Models;

use Database\Database;

class Sale
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getSales()
    {
        $query = "
		SELECT 
			DATE(s.sale_date) AS sale_date,
			COUNT(s.id) AS total_sales,
			SUM(s.quantity) AS total_quantity,
			SUM(s.sale_price) AS total_sale_amount,
			JSON_ARRAYAGG(
				JSON_BUILD_OBJECT(
					'time', TO_CHAR(s.sale_date, 'HH12:MI AM'), -- Format sale date for time
					'product', p.name,
					'quantity', s.quantity,
					'sale_amount', s.sale_price
				)
			) AS sale_details
		FROM sales s
		JOIN products p ON s.product_id = p.id -- Join with products to get names
		GROUP BY DATE(s.sale_date)
		ORDER BY sale_date DESC;
	";

        $statement = $this->db->prepare($query);
        $statement->execute();
        $sales = $statement->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($sales as &$sale) {
            $sale['sale_details'] = json_decode($sale['sale_details'], true);
        }

        return $sales;
    }

    public function createSale($data)
    {
        $productId = $data['product'];
        $quantity = $data['quantity'];
        $salePrice = $data['price'];
        $userID = $data['user_id'];

        $this->db->beginTransaction();

        try {
            $query = "
            INSERT INTO sales (user_id, product_id, quantity, sale_price)
            VALUES (:user_id, :product_id, :quantity, :sale_price);
        ";

            $statement = $this->db->prepare($query);
            $statement->bindValue(':user_id', $userID, \PDO::PARAM_INT);
            $statement->bindValue(':product_id', $productId, \PDO::PARAM_INT);
            $statement->bindValue(':quantity', $quantity, \PDO::PARAM_INT);
            $statement->bindValue(':sale_price', $salePrice, \PDO::PARAM_INT);
            $statement->execute();

            $updateInventoryQuery = "
            UPDATE inventory
            SET quantity = quantity - :quantity,
                on_hand = on_hand - :quantity
            WHERE product_id = :product_id;
        ";

            $updateStatement = $this->db->prepare($updateInventoryQuery);
            $updateStatement->bindValue(':quantity', $quantity, \PDO::PARAM_INT);
            $updateStatement->bindValue(':product_id', $productId, \PDO::PARAM_INT);
            $updateStatement->execute();

            $activityQuery = "
            INSERT INTO inventory_activities (inventory_plan_id, user_id, action)
            VALUES (NULL, :user_id, 'sale');
        ";

            $activityStatement = $this->db->prepare($activityQuery);
            $activityStatement->bindValue(':user_id', $userID, \PDO::PARAM_INT);
            $activityStatement->execute();

            $this->db->commit();

            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

}
