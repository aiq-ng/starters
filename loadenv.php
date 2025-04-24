<?php

function loadEnv($env = 'dev', $baseDir = __DIR__ . '/profiles')
{
    static $loaded = false;
    if ($loaded) {
        error_log("loadEnv: Already loaded, skipping");
        return true;
    }
    $loaded = true;

    $filePath = "$baseDir/$env/.env.app";

    if (!file_exists($filePath)) {
        error_log("loadEnv: Environment file not found at $filePath");
        return false;
    }

    if (!is_readable($filePath)) {
        error_log("loadEnv: Environment file at $filePath is not readable");
        return false;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        error_log("loadEnv: Failed to read environment file at $filePath");
        return false;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') {
            continue;
        }

        if (preg_match('/^([^=]+)=(.*)$/', $line, $matches)) {
            $key = trim($matches[1]);
            $value = trim($matches[2]);
            $value = preg_replace('/\s*#.*$/', '', $value);
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }

    return true;
}

$env = getenv('ENV') ?: 'dev';
error_log("loadEnv: Loading environment for $env");
loadEnv($env);
