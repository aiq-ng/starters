<?php

namespace Models;

use Database\Database;

class Vendor
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }


    public function create($data)
    {
        $query = "
			INSERT INTO vendors 
			(salutation, first_name, last_name, display_name, company_name, email, 
			work_phone, mobile_phone, address, social_media, payment_term_id, 
			currency_id, category_id, website) 
			VALUES 
			(:salutation, :first_name, :last_name, :display_name, :company_name, 
			:email, :work_phone, :mobile_phone, :address, :social_media, 
			:payment_term_id, :currency_id, :category_id, :website)
		";

        $stmt = $this->db->prepare($query);

        // Bind parameters
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
        $stmt->bindParam(':payment_term_id', $data['payment_term_id']);
        $stmt->bindParam(':currency_id', $data['currency_id']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':website', $data['website']);

        // Execute query
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    public function getVendor($id)
    {
        $query = "SELECT * FROM vendors WHERE id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getVendors()
    {
        $query = "
			SELECT 
				v.id,
				v.salutation || ' ' || v.first_name || ' ' || v.last_name AS name,
				vc.name AS category,
				v.email,
				v.work_phone,
				v.address,
				COALESCE(SUM(t.amount), 0) AS total_transaction,
				v.balance,
				v.status
			FROM 
				vendors v
			LEFT JOIN 
				vendor_transactions t 
			ON 
				v.id = t.vendor_id
			LEFT JOIN 
				vendor_categories vc
			ON 
				v.category_id = vc.id
			GROUP BY 
				v.id, vc.name
			ORDER BY 
				v.id ASC
		";

        $stmt = $this->db->prepare($query);

        if ($stmt->execute()) {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        return [];
    }


}
