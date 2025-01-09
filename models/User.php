<?php

namespace Models;

use Database\Database;

class User
{
    private $db;
    private $table = 'users';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }


    //Register new users
    public function register($data)
    {
        $query = "
        INSERT INTO " . $this->table . " 
        (firstname, lastname, email, password, role_id) 
        VALUES (:firstname, :lastname, :email, :password, :role_id)
        RETURNING id";
        $stmt = $this->db->prepare($query);

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $roleId = $data['role'] ?? 3;

        $stmt->bindParam(':firstname', $data['firstname']);
        $stmt->bindParam(':lastname', $data['lastname']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role_id', $roleId);

        if ($stmt->execute()) {
            return $stmt->fetchColumn();
        } else {
            return false;
        }
    }

    // Get User by ID or Email
    public function getUser($identifier)
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $query = "SELECT * FROM " . $this->table . " WHERE email = ? LIMIT 1";
        } else {
            $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 1";
        }

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $identifier, \PDO::PARAM_STR);

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        return null;
    }
}
