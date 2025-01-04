<?php

namespace Server;

require_once __DIR__ . '/../loadenv.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsServerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

loadEnv(__DIR__ . "/../.env");

class WebSocketServer implements MessageComponentInterface, WsServerInterface
{
    private static ?WebSocketServer $instance = null;
    protected $clients;
    protected $userConnections = [];
    private $secretKey;
    private $algorithm;
    private $amqpConnection;
    private $amqpChannel;
    private $amqpQueuePrefix;

    private function __construct()
    {
        $this->clients = new \SplObjectStorage();
        $this->secretKey = getenv('SECRET_KEY');
        $this->algorithm = getenv('ALGORITHM');

        // RabbitMQ setup
        $this->amqpConnection = new AMQPStreamConnection(
            getenv('RABBITMQ_HOST'),
            getenv('RABBITMQ_PORT'),
            getenv('RABBITMQ_USER'),
            getenv('RABBITMQ_PASS'),
            getenv('RABBITMQ_VHOST')
        );
        $this->amqpChannel = $this->amqpConnection->channel();
        $this->amqpQueuePrefix = 'ws_user_'; // Unique queue prefix for each user
    }

    public static function getInstance(): WebSocketServer
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $queryParams = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryParams, $params);

        if (isset($params['token'])) {
            $userId = $this->extractUserIdFromToken($conn);
            if ($userId) {
                // Store the connection in the userConnections array
                $this->userConnections[$userId] = $conn;
                $this->clients->attach($conn);

                // Subscribe to RabbitMQ queue for the user
                $this->amqpChannel->queue_declare(
                    $this->amqpQueuePrefix . $userId,
                    false,
                    true,
                    false,
                    false
                );
                $this->amqpChannel->basic_consume(
                    $this->amqpQueuePrefix . $userId,
                    '',
                    false,
                    true,
                    false,
                    false,
                    function ($msg) use ($conn) {
                        echo "Received message from RabbitMQ: " . $msg->body . "\n";
                        $conn->send($msg->body);
                    }
                );

                echo "User {$userId} connected with connection ID {$conn->resourceId}\n";

                // Start the consumer loop in a separate process
                $this->consumeMessagesAsync();
            } else {
                $conn->close();
                echo "Invalid token for connection {$conn->resourceId}\n";
            }
        } else {
            echo "Connection without token, still accepted for ping.\n";
        }
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
        // Unsubscribe the user from RabbitMQ
        foreach ($this->userConnections as $userId => $userConn) {
            if ($userConn === $conn) {
                unset($this->userConnections[$userId]);
                echo "User {$userId} disconnected\n";
                break;
            }
        }

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

    public function publishToRabbitMQ(string $userId, array $data): void
    {
        $message = json_encode($data);

        $msg = new AMQPMessage($message, [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]);

        $this->amqpChannel->basic_publish(
            $msg,
            '',
            $this->amqpQueuePrefix . $userId
        );

        echo "Message published to RabbitMQ for user {$userId}\n";
    }

    private function consumeMessagesAsync(): void
    {
        // Use pcntl_fork to spawn a separate process for the AMQP consumer
        $pid = pcntl_fork();

        if ($pid == -1) {
            echo "Could not create child process\n";
        } elseif ($pid) {
            // Parent process does nothing
            return;
        } else {
            // Child process starts consuming messages
            while ($this->amqpChannel->is_consuming()) {
                $this->amqpChannel->wait();
            }
            exit; // Terminate the child process when done
        }
    }
}
