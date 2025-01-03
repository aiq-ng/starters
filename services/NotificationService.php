<?php

namespace Services;

use Server\WebSocketServer;

class NotificationService
{
    private $webSocketServer;

    public function __construct()
    {
        // Retrieve the singleton instance of WebSocketServer
        $this->webSocketServer = WebSocketServer::getInstance();
    }

    public function sendNotification(string $userId, array $data): void
    {
        $userConnections = $this->webSocketServer->getUserConnections();

        error_log('User connections: ' . json_encode($userConnections));

        if (isset($userConnections[$userId])) {
            $connection = $userConnections[$userId];
            $connection->send(json_encode($data));

            error_log("Notification sent to user {$userId}");
        } else {
            error_log("User {$userId} is not connected to WebSocket server");
            echo json_encode([
                'message' => "User {$userId} is not connected to WebSocket server"
            ]);

        }
    }

    public function broadcastNotification(array $data): void
    {
        $userConnections = $this->webSocketServer->getUserConnections();

        foreach ($userConnections as $userId => $connection) {
            $connection->send(json_encode($data));
            echo "Notification broadcasted to user {$userId}\n";
        }
    }

    public function sendGroupNotification(array $userIds, array $data): void
    {
        $userConnections = $this->webSocketServer->getUserConnections();

        foreach ($userIds as $userId) {
            if (isset($userConnections[$userId])) {
                $connection = $userConnections[$userId];
                $connection->send(json_encode($data));

                echo "Notification sent to user {$userId}\n";
            } else {
                echo "User {$userId} is not connected to WebSocket server\n";
            }
        }
    }
}
