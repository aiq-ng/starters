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
    protected $clients;
    protected $userConnections = [];
    private $secretKey;
    private $algorithm;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
        $this->secretKey = getenv('SECRET_KEY');
        $this->algorithm = getenv('ALGORITHM');

    }

    public function onOpen(ConnectionInterface $conn)
    {
        $userId = $this->extractUserIdFromToken($conn);

        if ($userId) {
            $this->userConnections[$userId] = $conn;
            echo "User {$userId} connected with connection ID {$conn->resourceId}\n";
        } else {
            echo "Invalid or missing token for connection {$conn->resourceId}\n";
            $conn->close();
            return;
        }

        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo "Message received: {$msg}\n";

        $from->send("Received your message: {$msg}");
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
        echo "Token not provided in query parameters\n";
        return null;
    }
}
