<?php

// Start output buffering
if (!ob_get_level()) {
    ob_start();
}

header('Content-Type: application/json');

// Convert all warnings and notices to exceptions
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Global exception handler (catches all exceptions, including converted warnings)
set_exception_handler(function ($exception) {
    ob_clean();
    http_response_code(500);
    error_log('Exception: ' . $exception->getMessage());

    echo json_encode([
        'message' => 'Oops! Something went wrong. Please try again later.',
        'error' => $exception->getMessage()
    ]);

    exit;
});

// Handle fatal errors at shutdown (but prevent duplicate handling)
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        http_response_code(500);
        error_log("Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}");

        echo json_encode([
            'message' => "Fatal Error: {$error['message']}"
        ]);

        exit; // Stop further execution
    }
});
