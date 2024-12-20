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
        (name, salary_type, base_type_id, base_rate, base_salary, 
        work_leave_qualification, work_leave_period, description) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $data['name'],
            $data['salary_type'],
            $data['base_type_id'] ?? null,
            $data['base_rate'] ?? null,
            $data['base_salary'] ?? null,
            $data['work_leave_qualification'] ?? null,
            $data['work_leave_period'] ?? null,
            $data['description'] ?? null,
        ]);

        return $this->db->lastInsertId();
    }

    public function addEmployee($data, $mediaLinks)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users 
        (email, firstname, lastname, date_of_birth, address, next_of_kin,
        date_of_employment, department_id, role_id, no_of_working_days_id, 
        salary, bank_details, nin, passport, avatar_url, username, password) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $bankDetails = [
            'account_number' => $data['account_number'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
        ];
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);


        $stmt->execute([
            $data['email'] ?? null,
            $data['firstname'] ?? null,
            $data['lastname'] ?? null,
            $data['date_of_birth'] ?? null,
            $data['address'] ?? null,
            $data['next_of_kin'] ?? null,
            $data['date_of_employment'] ?? null,
            $data['department_id'] ?? null,
            $data['role_id'] ?? null,
            $data['no_of_working_days_id'] ?? null,
            $data['salary'] ?? null,
            json_encode($bankDetails),
            $mediaLinks['nin'][0] ?? null,
            $mediaLinks['passport'][0] ?? null,
            $mediaLinks['avatar_url'][0] ?? null,
            $data['username'] ?? null,
            $hashedPassword,
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

        $totalItems = ceil($totalCount / $pageSize);

        $meta = [
            'total_data' => (int) $totalItems,
            'total_pages' => ceil($totalItems / $pageSize),
            'page_size' => (int) $pageSize,
            'previous_page' => $page > 1 ? (int) $page - 1 : null,
            'current_page' => (int) $page,
            'next_page' => (int) $page + 1,
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

    public function addToLeave($employeeId, $data)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO user_leaves 
        (user_id, start_date, end_date, notes, leave_type) 
        VALUES (?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $employeeId,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['notes'] ?? null,
            $data['leave_type'] ?? 'annual'
        ]);

        return $this->db->lastInsertId();
    }

    public function putOnLeave($employeeId)
    {
        $stmt = $this->db->prepare(
            'UPDATE user_leaves 
            SET status = \'on leave\' 
            WHERE user_id = ?'
        );

        $stmt->execute([$employeeId]);

        return $stmt->rowCount();
    }

    public function suspendEmployee($employeeId)
    {
        $stmt = $this->db->prepare(
            'UPDATE users 
            SET status = \'inactive\' 
            WHERE id = ?'
        );

        $stmt->execute([$employeeId]);

        return $stmt->rowCount();
    }
}
