<?php

namespace Server;

require_once __DIR__ . '/../loadenv.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsServerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

loadEnv(__DIR__ . "/../.env");

class WebSocketServer implements MessageComponentInterface, WsServerInterface
{
    private static ?WebSocketServer $instance = null;
    protected $clients;
    protected $userConnections = [];
    private $secretKey;
    private $algorithm;

    private function __construct()
    {
        $this->clients = new \SplObjectStorage();
        $this->secretKey = getenv('SECRET_KEY');
        $this->algorithm = getenv('ALGORITHM');
    }

    public static function getInstance(): WebSocketServer
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone()
    {
    }
    public function __wakeup()
    {
    }

    public function getUserConnections(): array
    {
        return $this->userConnections;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $queryParams = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryParams, $params);

        if (isset($params['token'])) {
            $userId = $this->extractUserIdFromToken($conn);
            if ($userId) {
                $this->userConnections[$userId] = $conn;
                error_log("User {$userId} connected with connection ID {$conn->resourceId}");
                error_log("User connections: " . json_encode(array_keys($this->userConnections)));
            } else {
                echo "Invalid token for connection {$conn->resourceId}\n";
                $conn->close();
                return;
            }
        } else {
            echo "Connection without token, still accepted for ping.\n";
        }

        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $msg = trim($msg);
        echo "Message received: {$msg}\n";

        if (strtolower($msg) === "ping") {
            $from->send("pong");
        } else {
            $from->send("Received your message: {$msg}");
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        foreach ($this->userConnections as $userId => $userConn) {
            if ($userConn === $conn) {
                unset($this->userConnections[$userId]);
                echo "User {$userId} disconnected\n";
                break;
            }
        }

        echo "Connection {$conn->resourceId} has disconnected\n";
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Error: {$e->getMessage()}\n";
    }

    public function getSubProtocols()
    {
        return [];
    }

    private function extractUserIdFromToken(ConnectionInterface $conn)
    {
        $queryParams = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryParams, $params);

        if (isset($params['token'])) {
            try {
                $decoded = JWT::decode($params['token'], new Key($this->secretKey, $this->algorithm));

                if (isset($decoded->data) && isset($decoded->data->id)) {
                    return $decoded->data->id;
                } else {
                    echo "User ID not found in the decoded token\n";
                    return null;
                }
            } catch (\Exception $e) {
                echo "Invalid token: {$e->getMessage()}\n";
                return null;
            }
        }

        return null;
    }

    public function sendMessage(string $userId, array $data): void
    {
        error_log("Sending message to user {$userId}");
        error_log("User connections: " . json_encode(array_keys($this->userConnections)));

        if (isset($this->userConnections[$userId])) {
            $this->userConnections[$userId]->send(json_encode($data));
            echo "Message sent to user {$userId}\n";
        } else {
            echo "User {$userId} is not connected to WebSocket server\n";
        }
    }

    public function broadcast(array $data): void
    {
        foreach ($this->userConnections as $userId => $connection) {
            $connection->send(json_encode($data));
            echo "Message broadcasted to user {$userId}\n";
        }
    }
}
