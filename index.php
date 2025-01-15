<?php

// Handle CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, PATCH, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// If it's an OPTIONS request, return a response immediately
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit();
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/loadenv.php';

loadEnv(__DIR__ . "/.env");

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/routes/api.php';
