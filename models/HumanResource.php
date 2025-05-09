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

    public function getOverview($roleId = null)
    {
        try {
            $sql = "
                SELECT 
                    (SELECT COUNT(*) FROM users WHERE role_id != :roleId) AS employee_count,
                    (SELECT COUNT(*) FROM departments) AS department_count,
                    (SELECT COUNT(*) FROM users WHERE role_id = :roleId) AS admin_count,
                    (SELECT COUNT(*) FROM user_leaves WHERE status = 'on leave') AS on_leave_count
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':roleId', $roleId, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function createDepartment($data)
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO departments 
            (name, salary_type, base_type_id, base_rate, base_salary, 
            work_leave_qualification, work_leave_period, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            RETURNING id'
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

            $departmentId = $stmt->fetchColumn();

            return $departmentId;

        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Error creating department");
        }
    }

    public function addEmployee($data, $mediaLinks)
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO users 
            (email, firstname, lastname, date_of_birth, address, next_of_kin,
            date_of_employment, department_id, role_id, no_of_working_days_id, 
            salary, bank_details, nin, passport, avatar_url, username, password, emergency_contact) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            RETURNING id'
            );

            if (isset($data['login_details'])) {
                $data['username'] = $data['login_details']['username'] ?? $data['username'] ?? null;
                $data['password'] = $data['login_details']['password'] ?? $data['password'] ?? null;
            }

            if (!isset($data['bank_details'])) {
                $data['bank_details'] = [
                    'account_number' => $data['account_number'] ?? null,
                    'bank_name' => $data['bank_name'] ?? null,
                ];
            }

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
                json_encode($data['bank_details'] ?? []),
                $mediaLinks['nin'][0] ?? null,
                $mediaLinks['passport'][0] ?? null,
                $mediaLinks['avatar_url'][0] ?? null,
                $data['username'] ?? null,
                $hashedPassword,
                $data['emergency_contact'] ?? null,
            ]);

            $employeeId = $stmt->fetchColumn();

            return $employeeId;

        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Error adding employee");
        }
    }

    public function getAdmins($roleId = null)
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT u.id, u.name, r.name AS role
             FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.role_id = :roleId'
            );

            $stmt->bindValue(':roleId', $roleId, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getEmployees($filters = [])
    {
        try {
            $page = $filters['page'] ?? 1;
            $pageSize = $filters['page_size'] ?? 10;
            $offset = ($page - 1) * $pageSize;
            $adminId = $filters['role_id'] ?? null;

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
                WHERE u.role_id != :adminId
            ";

            $conditions = [];
            $params = [':adminId' => $adminId];

            if (!empty($filters['department']) && strtolower($filters['department']) !== 'all') {
                $conditions[] = "d.name = :department";
                $params[':department'] = $filters['department'];
            }

            if (!empty($filters['search'])) {
                $conditions[] = "(
                    u.name LIKE :search OR
                    r.name LIKE :search OR
                    d.name LIKE :search OR
                    u.bank_details::TEXT LIKE :search
                )";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            if ($conditions) {
                $sql .= ' AND ' . implode(' AND ', $conditions);
            }

            $sql .= ' LIMIT :pageSize OFFSET :offset';
            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, \PDO::PARAM_STR);
            }

            $stmt->bindValue(':pageSize', $pageSize, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $key => $employee) {
                $result[$key]['bank_details'] = !empty($employee['bank_details'])
                    ? json_decode($employee['bank_details'], true)
                    : null;
            }

            $totalCount = $this->countEmployees($filters);

            $meta = [
                'total_data' => (int)$totalCount,
                'total_pages' => ceil($totalCount / $pageSize),
                'page_size' => (int)$pageSize,
                'previous_page' => $page > 1 ? (int)$page - 1 : null,
                'current_page' => (int)$page,
                'next_page' => $page < ceil($totalCount / $pageSize) ? (int)$page + 1 : null,
            ];

            return [
                'data' => $result,
                'meta' => $meta
    ];
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return ['data' => [], 'meta' => []];
        }
    }

    public function countEmployees($filters = [])
    {
        try {
            $sql = "
                SELECT COUNT(*) AS total
                FROM users u
                LEFT JOIN departments d ON u.department_id = d.id
                LEFT JOIN roles r ON u.role_id = r.id
                LEFT JOIN user_leaves ul ON u.id = ul.user_id
                WHERE u.role_id != :adminId
            ";

            $conditions = [];
            $params = [':adminId' => $filters['role_id'] ?? null];

            if (!empty($filters['department'])) {
                $conditions[] = "d.name = :department";
                $params[':department'] = $filters['department'];
            }

            if (!empty($filters['search'])) {
                $conditions[] = "(
                u.name LIKE :search OR
                r.name LIKE :search OR
                d.name LIKE :search OR
                u.bank_details::TEXT LIKE :search
            )";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            if ($conditions) {
                $sql .= ' AND ' . implode(' AND ', $conditions);
            }

            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, \PDO::PARAM_STR);
            }

            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result['total'];
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getEmployee($employeeId)
    {
        try {
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
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function deleteEmployee($employeeIds)
    {
        try {
            if (empty($employeeIds)) {
                return false;
            }

            $employeeIds = is_array($employeeIds) ? $employeeIds : [$employeeIds];

            $placeholders = implode(',', array_fill(0, count($employeeIds), '?'));

            $stmt = $this->db->prepare("DELETE FROM users WHERE id IN ($placeholders)");
            $stmt->execute($employeeIds);

            return $stmt->rowCount();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Error deleting employee");
        }
    }

    public function updateEmployee($employeeid, $data)
    {
        if (isset($data['bank_details']) && is_array($data['bank_details'])) {
            $data['bank_details'] = json_encode($data['bank_details']);
        }
        $filtereddata = array_filter($data, function ($value) {
            return $value !== "" && $value !== null;
        });

        $setclauses = [];
        $params = [':employee_id' => $employeeid];

        foreach ($filtereddata as $field => $value) {
            $setclauses[] = "$field = :$field";
            $params[":$field"] = $value;
        }

        if (empty($setclauses)) {
            return null;
        }

        $setclausestring = implode(', ', $setclauses);

        $query = "
            update users
            set $setclausestring
            where id = :employee_id
            returning id;
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchcolumn();
        } catch (\exception $e) {
            throw new \exception("failed to update user: " . $e->getmessage());
        }
    }

    public function addToLeave($employeeId, $data)
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO user_leaves 
                (user_id, start_date, end_date, notes, leave_type) 
                VALUES (?, ?, ?, ?, ?) 
                RETURNING id'
            );

            $stmt->execute([
                $employeeId,
                $data['start_date'] ?? null,
                $data['end_date'] ?? null,
                $data['notes'] ?? null,
                $data['leave_type'] ?? 'annual'
            ]);

            return $stmt->fetchColumn() ?? null;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Error adding employee to leave");
        }
    }

    public function putOnLeave($employeeId)
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE user_leaves 
                SET status = \'on leave\' 
                WHERE user_id = ?'
            );

            $stmt->execute([$employeeId]);

            return $stmt->rowCount();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Error putting employee on leave");
        }
    }

    public function suspendEmployee($employeeId)
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE users 
                SET status = \'inactive\' 
                WHERE id = ?'
            );

            $stmt->execute([$employeeId]);

            return $stmt->rowCount();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Error suspending employee");
        }
    }
}
