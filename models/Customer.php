<?php

namespace Models;

use Database\Database;

class Customer
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }


    public function create($data)
    {
        $query = "
        INSERT INTO customers 
        (customer_type, salutation, first_name, last_name, display_name, 
        company_name, email, work_phone, mobile_phone, address, social_media, website)
        VALUES 
        (:customer_type, :salutation, :first_name, :last_name, :display_name, 
        :company_name, :email, :work_phone, :mobile_phone, :address, :social_media, :website)
    ";

        $stmt = $this->db->prepare($query);

        // Bind parameters
        $stmt->bindParam(':customer_type', $data['customer_type']);
        $stmt->bindParam(':salutation', $data['salutation']);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':display_name', $data['display_name']);
        $stmt->bindParam(':company_name', $data['company_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':work_phone', $data['work_phone']);
        $stmt->bindParam(':mobile_phone', $data['mobile_phone']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':social_media', $data['social_media']);
        $stmt->bindParam(':website', $data['website']);

        // Execute query
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    public function getCustomer($id)
    {
        $query = "SELECT * FROM customers WHERE id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getCustomers()
    {
        $query = "
            SELECT 
                c.id,
				c.salutation || ' ' || c.first_name || ' ' || c.last_name AS name,
                c.company_name,
                c.email,
                c.work_phone,
                c.address,
                COALESCE(SUM(t.amount), 0) AS total_transaction,
                c.balance,
                c.status
            FROM 
                customers c
            LEFT JOIN 
                customer_transactions t 
            ON 
                c.id = t.customer_id
            GROUP BY 
                c.id, c.first_name, c.last_name, c.company_name, c.email, 
                c.work_phone, c.address, c.balance, c.status
            ORDER BY 
                c.id ASC
        ";

        $stmt = $this->db->prepare($query);

        if ($stmt->execute()) {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        return [];
    }


}
