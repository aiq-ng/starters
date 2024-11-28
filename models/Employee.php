<?php

namespace Models;

use Database\Database;

class Employee
{
    private $db;
    private $table = 'employees';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }


    //Register new admins
    public function registerEmployee($data, $files = [])
    {
      $this->db->beginTransaction();
      try {
        $query = "INSERT INTO " . $this->table . "(firstname, lastname, department, salaries, bank_details, date_of_birth, leave, date_of_employment, nin, passport)
        VALUES (:firstname, :lastname, :department, :salaries, :bank_details, :date_of_birth, :leave, :date_of_employment, :nin, :passport)";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':firstname', $data['firstname']);
        $stmt->bindValue(':lastname', $data['lastname']);
        $stmt->bindValue(':department', $data['department']);
        $stmt->bindValue(':salaries', $data['salaries']);
        $stmt->bindValue(':bank_details', json_encode($data['bank_details']));
        $stmt->bindValue(':date_of_birth', $data['date_of_birth']);
        $stmt->bindValue(':leave', $data['leave']);
        $stmt->bindValue(':date_of_employment', $data['date_of_employment']);
        $stmt->bindValue(':nin', json_encode($files) ?? null);
        $stmt->bindValue(':passport', json_encode($files) ?? null);

        if(!$stmt->execute()) {
            throw new \Exception('Failed to register Employee');
        }
        $this->db->commit();
        return $this->db->lastInsertId();

        
      }
      catch (\PDOException $e) {
        $this->db->rollBack();
        error_log($e->getMessage());
        throw new \Exception('Database error: ' . $e->getMessage());
    }
}

public function getAllEmployees($page, $pageSize) {

    $offset = ($page - 1) * $pageSize;

    $query = "SELECT id, CONCAT(first_name, ' ', last_name) AS name, department, salary, bank_details, leave_date, passport_path 
                  FROM " . $this->table . " 
                  ORDER BY id ASC 
                  LIMIT :perPage OFFSET :offset";

                  $stmt = $this->db->prepare($query);
                  $stmt->bindParam(':perPage', $pageSize, \PDO::PARAM_INT);
                  $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
                  if (!$stmt->execute()) {
                    throw new \Exception('Failed to fetch employees.');
                }
                  return $stmt->fetchAll(\PDO::FETCH_ASSOC);

                  foreach ($employees as &$employee) {
                    $employee['bank_details'] = json_decode($employee['bank_details'], true);
                    $employee['passport'] = json_decode($employee['passport'], true);
                }
            
                return $employees;
 }

 public function countEmployees() {
    $query = "SELECT COUNT(*) AS total FROM " . $this->table;
    $stmt = $this->db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);

    return $result['total'] ?? 0;
    
}

public function getEmployeeById($id) {
    $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(\PDO::FETCH_ASSOC);
}

public function deleteEmployee($id) {
    $query = "DELETE FROM " . $this->table . " WHERE id = :id";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':id', $id, \PDO::PARAM_INT);

    if (!$stmt->execute()) {
        throw new \Exception('Failed to delete employee.');
    }
    if (!$stmt->rowCount()) {
        throw new \Exception('No employee found to delete.');
       
    }

    return true;
}


}

