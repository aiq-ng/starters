<?php

require __DIR__ . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$webSocketServer = Server\WebSocketServer::getInstance();

$port = 8090;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            $webSocketServer
        )
    ),
    $port
);

echo "WebSocket server running on ws://localhost:{$port}\n";
$server->run();
