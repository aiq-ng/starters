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
    } elseif (strpos($message, 'foreign key constraint') !== false || strpos($message, 'SQLSTATE[23503]') !== false) {
        // Handle foreign key constraint violations
        http_response_code(400); // Bad Request
        error_log("Foreign Key Constraint Violation: $message in $file on line $line");
        echo json_encode([
            'message' => 'Foreign Key Constraint Violation: The referenced record does not exist. Please ensure that all references are valid.'
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

        // Check if it's a foreign key constraint violation
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
