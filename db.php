<?php

require_once __DIR__ . '/vendor/autoload.php';

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $conn;

    public function __construct()
    {
        $this->host = getenv('DB_HOST');
        $this->db_name = getenv('DB_NAME');
        $this->username = getenv('DB_USERNAME');
        $this->password = getenv('DB_PASSWORD');
        $this->port = getenv('DB_PORT');
    }

    public function getConnection()
    {
        $this->conn = null;

        try {
            // Create the PDO connection string
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name};";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "DB Connected successfully";
        } catch (PDOException $exception) {
            echo "Connection failed: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
