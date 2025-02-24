<?php

namespace Models;

use Database\Database;

class Admin
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function addAdminAccess($userId, $data)
    {
        $stmt = $this->db->prepare(
            'UPDATE users 
        SET password = ? 
        WHERE email = ?'
        );

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        try {
            $stmt->execute([
                $hashedPassword,
                $data['email']
            ]);

            if (!empty($data['permissions']) && is_array($data['permissions'])) {
                $this->insertPermissions($userId, $data['permissions']);
            }

            return $userId;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception('Error adding admin access');
        }
    }

    public function insertPermissions($userId, $permissions)
    {
        try {
            foreach ($permissions as $permissionId) {
                $stmt = $this->db->prepare(
                    'INSERT INTO user_permissions (user_id, permission_id) 
            VALUES (?, ?)'
                );
                $stmt->execute([$userId, $permissionId]);
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception('Error adding permissions');
        }
    }

    public function getPermissionByUserCount()
    {
        $stmt = $this->db->prepare(
            'SELECT p.name AS permission_name, COUNT(up.user_id) AS total_users
            FROM user_permissions up
            JOIN permissions p ON up.permission_id = p.id
            GROUP BY p.name'
        );

        try {
            $stmt->execute();
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $overview = [];
            foreach ($results as $row) {
                $overview[$row['permission_name']] = $row['total_users'];
            }

            return $overview;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
