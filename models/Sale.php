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
            $sale['sale_details'] = json_decode($sale['sale_details'], true); // Decode to associative array
        }

        return $sales;
    }

}
