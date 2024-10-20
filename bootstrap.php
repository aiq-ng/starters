<?php

// Start output buffering
ob_start();

header('Content-Type: application/json');

set_error_handler(function ($severity, $message, $file, $line) {
    // Clear any existing output
    ob_clean();

    // Check if the error message indicates a duplicate key or unique constraint violation
    if (strpos($message, 'duplicate key') !== false || strpos($message, 'unique constraint') !== false) {
        http_response_code(409); // Conflict
        error_log("Unique Constraint Violation: $message in $file on line $line");
        echo json_encode([
            'message' => 'A record with the same value already exists. Please use a different value.'
        ]);
    } else {
        http_response_code(500);
        error_log("Error: $message in $file on line $line");
        echo json_encode([
            'message' => "Error: $message in $file on line $line"
        ]);
    }

    exit;
});

set_exception_handler(function ($exception) {
    ob_clean();
    http_response_code(500);
    error_log('Exception: ' . $exception->getMessage());
    echo json_encode([
        'message' => $exception->getMessage()
    ]);
    exit;
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        ob_clean();
        http_response_code(500);
        error_log("Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}");
        echo json_encode([
            'message' => "Fatal Error: {$error['message']}"
        ]);
        exit;
    }
});
