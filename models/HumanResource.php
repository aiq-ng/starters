<?php

namespace Models;

use Database\Database;

class HumanResource
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getOverview()
    {
        $sql = "
            SELECT 
                (SELECT COUNT(*) FROM users WHERE role_id != 1) AS employee_count,
                (SELECT COUNT(*) FROM departments) AS department_count,
                (SELECT COUNT(*) FROM users WHERE role_id = 1) AS admin_count,
                (SELECT COUNT(*) FROM user_leaves WHERE status = 'on leave') AS on_leave_count
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function createDepartment($data)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO departments 
        (name, salary_type, base_type_id, base_rate, base_salary, description) 
        VALUES (?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $data['name'],
            $data['salary_type'],
            $data['base_type_id'] ?? null,
            $data['base_rate'] ?? null,
            $data['base_salary'] ?? null,
            $data['description'] ?? null,
        ]);

        return $this->db->lastInsertId();
    }

    public function getAdmins()
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.name, r.name AS role
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.role_id = 1'
        );

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getEmployees($filters = [])
    {
        $page = $filters['page'] ?? 1;
        $pageSize = $filters['page_size'] ?? 10;
        $offset = ($page - 1) * $pageSize;

        $sql = "
            SELECT
                u.id,
                u.name,
                u.avatar_url,
                d.name AS department,
                r.name AS position,
                u.salary,
                u.bank_details,
                COALESCE(ul.status, 'none') AS leave_status
            FROM users u
            LEFT JOIN departments d ON u.department_id = d.id
            LEFT JOIN roles r ON u.role_id = r.id
            LEFT JOIN user_leaves ul ON u.id = ul.user_id
            WHERE u.role_id != 1
        ";

        if (!empty($filters['department'])) {
            $department = $filters['department'];
            $sql .= " AND d.name = :department";
        }

        $sql .= " LIMIT :pageSize OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        // Bind parameters
        if (!empty($filters['department'])) {
            $stmt->bindParam(':department', $department, \PDO::PARAM_STR);
        }
        $stmt->bindParam(':pageSize', $pageSize, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);

        $stmt->execute();

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $key => $employee) {
            $result[$key]['bank_details'] = json_decode($employee['bank_details']);
        }

        $totalCount = $this->countEmployees($filters);

        $totalPages = ceil($totalCount / $pageSize);

        $meta = [
            'current_page' => (int) $page,
            'page_size' => (int) $pageSize,
            'total_data' => (int) $totalCount,
            'total_pages' => $totalPages
        ];

        return [
            'data' => $result,
            'meta' => $meta
        ];
    }

    public function countEmployees($filters = [])
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM users u
            LEFT JOIN departments d ON u.department_id = d.id
            LEFT JOIN roles r ON u.role_id = r.id
            LEFT JOIN user_leaves ul ON u.id = ul.user_id
            WHERE u.role_id != 1
        ";

        if (!empty($filters['department'])) {
            $department = $filters['department'];
            $sql .= " AND d.name = :department";
        }

        $stmt = $this->db->prepare($sql);

        if (!empty($filters['department'])) {
            $stmt->bindParam(':department', $department, \PDO::PARAM_STR);
        }

        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['total'];
    }

    public function getEmployee($employeeId)
    {
        $stmt = $this->db->prepare(
            'SELECT 
                u.id,
                u.avatar_url,
                u.name,
                u.firstname,
                u.lastname,
                u.email,
                u.date_of_birth,
                u.nin,
                u.passport,
                u.avatar_url,
                d.name AS department,
                r.name AS position,
                u.salary,
                u.bank_details,
                u.created_at,
                COALESCE(ul.status, \'none\') AS leave_status
            FROM users u
            LEFT JOIN departments d ON u.department_id = d.id
            LEFT JOIN roles r ON u.role_id = r.id
            LEFT JOIN user_leaves ul ON u.id = ul.user_id
            WHERE u.id = ?'
        );

        $stmt->execute([$employeeId]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            if ($result['bank_details']) {
                $result['bank_details'] = json_decode($result['bank_details']);
            } else {
                $result['bank_details'] = [];
            }
        }

        return $result;
    }

    public function deleteEmployee($employeeId)
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$employeeId]);

        return $stmt->rowCount();
    }
}
