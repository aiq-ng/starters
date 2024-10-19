<?php

require_once __DIR__ . '/vendor/autoload.php';

class Database
{
    private static $instance = null;
    private $conn;
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;

    private function __construct()
    {
        $this->host = getenv('DB_HOST');
        $this->db_name = getenv('DB_NAME');
        $this->username = getenv('DB_USERNAME');
        $this->password = getenv('DB_PASSWORD');
        $this->port = getenv('DB_PORT');

        try {
            // Create the PDO connection string
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name};";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "DB Connected successfully";
        } catch (PDOException $exception) {
            echo "Connection failed: " . $exception->getMessage();
        }
    }

    // Singleton method to get the instance
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }
}

$db = Database::getInstance()->getConnection();
