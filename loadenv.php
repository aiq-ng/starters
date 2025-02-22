<?php

function loadEnv($filePath = __DIR__ . '/.env')
{
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
        // Skip comments and invalid lines
        $line = trim($line);
        if (empty($line) || $line[0] === '#') {
            continue;
        }

        // Match key=value, ignoring comments after the value
        if (preg_match('/^([^=]+)=(.*)$/', $line, $matches)) {
            $key = trim($matches[1]);
            $value = trim($matches[2]);
            // Remove inline comments from value
            $value = preg_replace('/\s*#.*$/', '', $value);
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
    return true;
}

loadEnv();
