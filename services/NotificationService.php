<?php

namespace Services;

use Server\WebSocketServer;

class NotificationService
{
    private $webSocketServer;

    public function __construct()
    {
        $this->webSocketServer = WebSocketServer::getInstance();
    }

    public function sendNotification(string $userId, array $data): void
    {
        $this->webSocketServer->publishToRabbitMQ($userId, $data);
    }

    public function broadcastNotification(array $data): void
    {
        $this->webSocketServer->broadcast($data);
    }

    public function sendGroupNotification(array $userIds, array $data): void
    {
        foreach ($userIds as $userId) {
            $this->webSocketServer->sendMessage($userId, $data);
        }
    }
}
