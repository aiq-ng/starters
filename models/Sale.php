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
				DATE(s.sale_date) AS date,
				COUNT(s.id) AS sales,
				SUM(s.quantity) AS quantities,
				SUM(s.sale_price) AS revenue,
				COALESCE(expenses.total_cost, 0) AS expenses,
				(SUM(s.sale_price) - COALESCE(expenses.total_cost, 0)) AS profit,
				JSON_ARRAYAGG(
					JSON_BUILD_OBJECT(
						'time', TO_CHAR(s.sale_date, 'HH12:MI AM'),
						'product', p.name,
						'quantity', s.quantity,
						'sale_amount', s.sale_price
					)
				) AS sale_details
			FROM sales s
			JOIN products p ON s.product_id = p.id
			LEFT JOIN (
				SELECT 
					DATE(p.purchase_date) AS expense_date,
					SUM(pi.total_price) AS total_cost
				FROM purchase_items pi
				JOIN purchases p ON pi.purchase_id = p.id
				GROUP BY DATE(p.purchase_date)
			) expenses ON DATE(s.sale_date) = expenses.expense_date
			GROUP BY DATE(s.sale_date), expenses.total_cost
			ORDER BY s.sale_date DESC;
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
        $saleDate = $data['date'];

        $this->db->beginTransaction();

        try {
            $this->insertSale($userID, $productId, $quantity, $salePrice, $saleDate);
            $this->updateInventory($productId, $quantity);
            $this->logActivity($userID);

            $this->db->commit();

            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function insertSale($userID, $productId, $quantity, $salePrice, $saleDate)
    {
        $query = "
			INSERT INTO sales (user_id, product_id, quantity, sale_price, sale_date)
			VALUES (:user_id, :product_id, :quantity, :sale_price, :sale_date);
		";

        $statement = $this->db->prepare($query);
        $statement->bindValue(':user_id', $userID, \PDO::PARAM_INT);
        $statement->bindValue(':product_id', $productId, \PDO::PARAM_INT);
        $statement->bindValue(':quantity', $quantity, \PDO::PARAM_INT);
        $statement->bindValue(':sale_price', $salePrice, \PDO::PARAM_INT);
        $statement->bindValue(':sale_date', $saleDate);
        $statement->execute();
    }

    private function updateInventory($productId, $quantity)
    {
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
    }

    private function logActivity($userID)
    {
        $activityQuery = "
			INSERT INTO inventory_activities (inventory_plan_id, user_id, action)
			VALUES (NULL, :user_id, 'sale');
		";

        $activityStatement = $this->db->prepare($activityQuery);
        $activityStatement->bindValue(':user_id', $userID, \PDO::PARAM_INT);
        $activityStatement->execute();
    }

}
