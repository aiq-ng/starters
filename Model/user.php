<?php
require_once __DIR__.'/../db.php';

Class User {
    private $conn;
    private $table = 'users';
    
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    //Register new users

    public function register($username, $email, $password) {
        $query = "INSERT INTO " . $this->table . " (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $this->conn->prepare ($query);
      
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
       

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    //Check if Email exists
    public function emailExists($email) {
        $query = "SELECT username, email, password FROM " . $this->table . " WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $username = $row['username'];
            $email = $row['email'];
            $password = $row['password'];
               
            return true;
        }

        return false;
    }
       
        

    //Login users

    public function login ($email, $password) {
        $query = 'SELECT * FROM '. $this->table . ' WHERE email= :email';
        $stmt = $this->conn->prepare ($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        } else {
            return false;
    }
}

}
