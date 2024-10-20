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
            (name, email, password, role_id) VALUES (:name, :email, :password, :role_id)";
        $stmt = $this->db->prepare($query);

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role_id', $data['role']) ?? 3;


        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    // Get User by ID or Email
    public function getUser($identifier)
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $query = "SELECT * FROM " . $this->table . " WHERE email = ? LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $identifier, \PDO::PARAM_STR);
        } elseif (is_numeric($identifier)) {
            $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $identifier, \PDO::PARAM_INT);
        } else {
            return null;
        }

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row;
        }

        return null;
    }
}
