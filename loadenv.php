<?php

function loadEnv($filePath)
{
    if (!file_exists($filePath)) {
        return;
    }

    $env = file_get_contents($filePath);
    $lines = explode("\n", $env);

    foreach ($lines as $line) {
        preg_match("/([^#]+)=(.*)/", $line, $matches);
        if (isset($matches[2])) {
            putenv(trim($line));
        }
    }
}
