<?php

use Controllers\AuthController;
use Controllers\ProductController;
use Controllers\WarehouseController;

// Create instances of the controllers
$authController = new AuthController();
$productController = new ProductController();
$warehouseController = new WarehouseController();

// Define routes
$routes = [
    'GET' => [
        '/' => function () {
            echo json_encode(['status' => 'ok']);
            exit;
        },
        '/auth/login' => [$authController, 'login'],
        '/auth/logout' => [$authController, 'logout'],
        '/products' => [$productController, 'getAll'],
        '/products/lowstockalerts' => [$productController, 'getLowStockAlerts'],
        '/products/warehouseno' => [$productController, 'getWhNo'],
        '/products/warehouseitems' => [$productController, 'getWhItems'],
        '/products/warehousea' => [$productController, 'getWhA'],
        '/products/warehouseb' => [$productController, 'getWhB'],
        '/products/lowstockalertsa' => [$productController, 'getLowStockAlertsA'],
        '/products/(\d+)' => [$productController, 'get'],
    ],
    'POST' => [
        '/auth/register' => [$authController, 'register'],
        '/products' => [$productController, 'create'],
        '/warehouse' => [$warehouseController, 'createWh'],
    ],
    'PUT' => [
        '/products/(\d+)' => [$productController, 'update'],
    ],
    'DELETE' => [
        '/products/(\d+)' => [$productController, 'delete'],
    ],
];

// Get the request method and URI
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// Dispatch the request
$found = false;
foreach ($routes[$requestMethod] as $route => $handler) {
    if (preg_match("#^$route$#", $requestUri, $matches)) {
        $found = true;
        if (is_array($handler)) {
            $id = isset($matches[1]) ? $matches[1] : null;
            if ($id !== null) {
                call_user_func($handler, $id);
            } else {
                call_user_func($handler);
            }
        } else {
            call_user_func($handler);
        }
        break;
    }
}

// Handle invalid requests
if (!$found) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Invalid Request']);
}
