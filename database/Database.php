<?php

namespace Database;

use PDO;
use PDOException;

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
        $this->host = getenv('DB_HOST') ?: 'database';
        $this->db_name = getenv('DB_NAME') ?: 'starters';
        $this->user = getenv('DB_USER') ?: 'postgres';
        $this->password = getenv('DB_PASSWORD') ?: 'starterssecret';
        $this->port = getenv('DB_PORT') ?: '5432';

        if (!$this->host || !$this->db_name || !$this->user || !$this->port) {
            $errorMsg = "Missing required environment variables: " .
                        "host=$this->host, db=$this->db_name, user=$this->user, port=$this->port";
            error_log($errorMsg);
            throw new \Exception($errorMsg);
        }

        $this->conn = $this->createConnection();
    }

    private function createConnection()
    {
        $maxRetries = 3;
        $retryDelay = 2; // seconds
        $attempt = 1;

        while ($attempt <= $maxRetries) {
            try {
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name};";
                $pdo = new PDO($dsn, $this->user, $this->password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);

                return $pdo;
            } catch (PDOException $exception) {
                $errorMsg = "Attempt $attempt failed: " . $exception->getMessage() . " (DSN: $dsn)";
                error_log($errorMsg);

                if ($attempt === $maxRetries) {
                    $finalError = "Database connection failed after $maxRetries attempts: " . $exception->getMessage();
                    error_log($finalError);
                    throw new PDOException($finalError, (int)$exception->getCode(), $exception);
                }

                sleep($retryDelay);
                $attempt++;
            }
        }
    }

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

    public static function getNewConnection()
    {
        return (new self())->getConnection();
    }
}

$db = Database::getInstance()->getConnection();
