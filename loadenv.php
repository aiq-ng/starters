<?php

function loadEnv($env = 'dev', $baseDir = __DIR__ . '/profiles')
{
    loadRootEnv();

    $env = getenv('ENV') ?: $env;

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

function loadRootEnv()
{
    $rootEnvPath = __DIR__ . '/.env';
    if (file_exists($rootEnvPath)) {
        $lines = file($rootEnvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
    }
}

loadRootEnv();

$env = getenv('ENV') ?: 'dev';
loadEnv($env);

