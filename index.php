<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/loadenv.php';

loadEnv(__DIR__ . "/.env");

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/routes/api.php';
