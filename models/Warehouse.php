<?php

namespace Models;

use Database\Database;

class Warehouse
{
    private $db;

    private $warehouse = 'warehouses';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }


    //Create Warehouse and Store Address

    public function create($data)
    {
        $query = "INSERT INTO " . $this->warehouse . "
		(name, address) VALUES (:name, :address)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':address', $data['address']);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return null;
    }

    //Get Warehouse information
    public function getWarehouse($id)
    {
        $query = "SELECT * FROM " . $this->warehouse . " WHERE id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    //Update Warehouse information
    public function getWarehouses()
    {
        $query = "
            SELECT 
                w.id,
                w.name,
                w.address,
                COUNT(DISTINCT i.product_id) AS total_products,
                COALESCE(SUM(i.quantity), 0) AS total_product_units
            FROM 
                " . $this->warehouse . " w
            LEFT JOIN 
                inventory i ON w.id = i.warehouse_id
            LEFT JOIN 
                products p ON i.product_id = p.id
            GROUP BY 
                w.id, w.name, w.address
            ORDER BY 
                w.id;
        ";

        $stmt = $this->db->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    //Get Warehouse Products
    public function getWarehouseProducts($id)
    {
        $query = "SELECT p.id AS product_id, 
			p.name AS product_name, 
			p.code AS product_code, 
			p.price, 
			p.quantity, 
			i.quantity AS quantity_in_warehouse
		FROM products p
		JOIN inventory i ON p.id = i.product_id
		JOIN warehouses w ON i.warehouse_id = w.id
		WHERE w.id = :warehouse_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':warehouse_id', $id);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
