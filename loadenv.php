<?php

function loadEnv($filePath)
{
    $env = file_get_contents($filePath);
    $lines = explode("\n", $env);

    foreach ($lines as $line) {
        preg_match("/([^#]+)=(.*)/", $line, $matches);
        if (isset($matches[2])) {
            putenv(trim($line));
        }
    }
}
