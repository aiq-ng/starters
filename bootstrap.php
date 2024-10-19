<?php

// Start output buffering
ob_start();

// Set Content-Type to JSON for all responses
header('Content-Type: application/json');

// Custom error handler to return JSON responses
set_error_handler(function ($severity, $message, $file, $line) {
    // Clear any existing output
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'message' => "Error: $message in $file on line $line"
    ]);
    exit;
});

// Custom exception handler to return JSON responses
set_exception_handler(function ($exception) {
    // Clear any existing output
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'message' => $exception->getMessage()
    ]);
    exit;
});

// Custom shutdown function to catch fatal errors
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        // Clear any existing output
        ob_clean();
        http_response_code(500);
        echo json_encode([
            'message' => "Fatal Error: {$error['message']}"
        ]);
        exit;
    }
});
