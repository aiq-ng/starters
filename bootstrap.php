<?php

// Start output buffering
if (!ob_get_level()) {
    ob_start();
}

header('Content-Type: application/json');

// Convert all PHP warnings, notices, and errors into exceptions
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false; // Skip errors that should not be reported
    }

    throw new \ErrorException($message, 0, $severity, $file, $line);
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

        if (strpos($error['message'], 'SQLSTATE[23503]') !== false) {
            http_response_code(400); // Bad Request
            error_log("Foreign Key Constraint Violation: {$error['message']} in {$error['file']} on line {$error['line']}");
            echo json_encode([
                'message' => 'Foreign Key Constraint Violation: The referenced record does not exist. Please ensure that all references are valid.'
            ]);
        } else {
            http_response_code(500);
            error_log("Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}");
            echo json_encode([
                'message' => "Fatal Error: {$error['message']}"
            ]);
        }

        exit;
    }
});
