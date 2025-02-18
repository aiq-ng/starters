<?php

namespace Services;

require_once __DIR__ . '/database/Database.php';

use Database\Database;

class ListenerService
{
    private $db;

    public function __construct()
    {
        try {
            $this->db = Database::getInstance()->getConnection();
            $this->db->exec("LISTEN availability_channel");
            echo "Listening for availability updates...\n";
        } catch (\PDOException $e) {
            die("Database error: " . $e->getMessage());
        }
    }

    public function listen(): void
    {
        while (true) {
            try {
                // Keeps the connection alive
                $stmt = $this->db->query("SELECT 1");
                $stmt->fetch();

                // Fetch notifications
                $notify = $this->db->pgsqlGetNotify(\PDO::FETCH_ASSOC, 1000); // 1s timeout

                if ($notify) {
                    echo "Notification received: " . $notify['message'] . "\n";

                    $payload = json_decode($notify['message'], true);
                    if ($payload) {
                        $this->handleNotification($payload);
                    }
                }

                // Prevent high CPU usage
                usleep(100000); // 100ms
            } catch (\PDOException $e) {
                echo "Error processing notification: " . $e->getMessage() . "\n";
            }
        }
    }

    private function handleNotification(array $payload): void
    {
        echo "Item ID: " . $payload['item_id'] . "\n";
        echo "Name: " . $payload['name'] . "\n";
        echo "SKU: " . $payload['sku'] . "\n";
        echo "Availability: " . $payload['availability'] . "\n";
        echo "Updated At: " . $payload['updated_at'] . "\n";
    }
}
