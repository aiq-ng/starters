<?php

require_once __DIR__ . '/database/Database.php';
require_once __DIR__ . '/services/ListenerService.php';
require_once __DIR__ . '/loadenv.php';

use Services\ListenerService;

// Start the listener service
$listener = new ListenerService();
$listener->listen();
