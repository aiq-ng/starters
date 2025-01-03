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
    public $userConnections = [];
    private $secretKey;
    private $algorithm;

    // Private constructor to prevent direct instantiation
    private function __construct()
    {
        $this->clients = new \SplObjectStorage();
        $this->secretKey = getenv('SECRET_KEY');
        $this->algorithm = getenv('ALGORITHM');
    }

    // Public static method to get the singleton instance
    public static function getInstance(): WebSocketServer
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Prevent cloning of the instance
    private function __clone()
    {
    }

    // Prevent unserializing of the instance
    public function __wakeup()
    {
    }

    public function getUserConnections(): array
    {
        return $this->userConnections;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Allowing connection without requiring token for "ping"
        $queryParams = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryParams, $params);

        if (isset($params['token'])) {
            $userId = $this->extractUserIdFromToken($conn);
            if ($userId) {
                $this->userConnections[$userId] = $conn;
                echo "User {$userId} connected with connection ID {$conn->resourceId}\n";
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
                    $userId = $decoded->data->id;
                    return $userId;
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
}
