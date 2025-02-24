<?php

namespace Database;

class Database
{
    private static $instance = null;
    private $conn;
    private $host;
    private $db_name;
    private $user;
    private $password;
    private $port;

    private function __construct()
    {
        if (getenv('ENV') === 'dev') {
            $this->host = '127.0.0.1';
            $this->db_name = 'startersdb';
            $this->user = 'postgres';
            $this->password = 'postgres';
            $this->port = '5432';
        } else {
            $this->host = getenv('DB_HOST');
            $this->db_name = getenv('DB_NAME');
            $this->user = getenv('DB_USER');
            $this->password = getenv('DB_PASSWORD');
            $this->port = getenv('DB_PORT');
        }

        $this->conn = $this->createConnection();
    }

    private function createConnection()
    {
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name};";
            $pdo = new \PDO($dsn, $this->user, $this->password);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (\PDOException $exception) {
            echo "Connection failed: " . $exception->getMessage();
            throw new \PDOException("Database connection failed: " .
                $exception->getMessage(), $exception->getCode());
        }
    }

    // Singleton method to get the shared connection
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

    // Method to get a new connection (for cron jobs, separate tasks, etc.)
    public static function getNewConnection()
    {
        return (new self())->getConnection();
    }
}

$db = Database::getInstance()->getConnection();
