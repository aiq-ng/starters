<?php

namespace Models;

use Database\Database;

class Admin
{
    private $db;
    private $table = 'admins';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }


    //Register new admins

    // Validate permissions value
    private function isValidPermission($permission)
    {
        $validPermissions = ['Accountant', 'HR', 'Inventory Manager', 'Sales'];
        return in_array($permission, $validPermissions);
    }
    public function registerAdmins($data)
    {

        if (!$this->isValidPermission($data['permissions'])) {
            throw new \InvalidArgumentException("Invalid permissions value");
        }
        $role = isset($data['role']) ? $data['role'] : 1;
        $query = "
            INSERT INTO " . $this->table . " 
            (username, password, role_id, permissions) VALUES (:username, :password, :role_id, :permissions)";
        $stmt = $this->db->prepare($query);

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role_id', $data['role']) ?? 1;
        $stmt->bindParam(':permissions', $data['permissions']);


        if ($stmt->execute()) {
            return $this->db->lastInsertId();
      
        } else {
            throw new \Exception("Error inserting admin");
        }
    }


    //Get Admin by username 

    public function getAdmin($identifier)
    {

          // Ensure identifier is not empty
        if (empty($identifier)) {
            throw new \InvalidArgumentException("Invalid identifier provided");
            }

            $query = "SELECT * FROM " . $this->table . " WHERE username = :identifier LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':identifier', $identifier, \PDO::PARAM_STR);
       
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row;
        }

        return null;
    }


    // Get number of Admins by permissions
    public function getNumberOfAdmins()
    {   
            $query = "SELECT permissions, COUNT(*) AS admin_count FROM " . $this->table . "GROUP BY permissions";
            $stmt = $this->db->prepare($query);

            try {
                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                return $result;
            }
            catch (\PDOException $e) {
                error_log($e->getMessage());
                throw new \Exception("Error fetching admin data");
                // return [];
            }
    }
}
