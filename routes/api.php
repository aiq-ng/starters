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
        '/roles' => [$authController, 'getRoles'],
        '/products' => [$productController, 'index'],
        '/products/count' => [$productController, 'count'],
        '/vendors' => [$productController, 'getVendors'],
        '/units' => [$productController, 'getUnits'],
        '/products/lowstockalerts' => [$productController, 'getLowStockAlerts'],
        '/products/warehouseno' => [$productController, 'getWhNo'],
        '/products/warehouseitems' => [$productController, 'getWhItems'],
        '/products/warehousea' => [$productController, 'getWhA'],
        '/products/warehouseb' => [$productController, 'getWhB'],
        '/products/lowstockalertsa' => [$productController, 'getLowStockAlertsA'],
        '/products/(\d+)' => [$productController, 'show'],
    ],
    'POST' => [
        '/auth/register' => [$authController, 'register'],
        '/products' => [$productController, 'create'],
        '/warehouses' => [$warehouseController, 'create'],
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

// Handle query parameters for pagination
$queryParams = [];
if (strpos($requestUri, '?') !== false) {
    list($requestUri, $queryString) = explode('?', $requestUri, 2);
    parse_str($queryString, $queryParams);
}

// Dispatch the request
$found = false;
foreach ($routes[$requestMethod] as $route => $handler) {
    if (preg_match("#^$route$#", $requestUri, $matches)) {
        $found = true;
        if (is_array($handler)) {
            $id = isset($matches[1]) ? $matches[1] : null;
            if ($id !== null) {
                call_user_func($handler, $id, $queryParams);
            } else {
                call_user_func($handler, $queryParams);
            }
        } else {
            call_user_func($handler);
        }
        break;
    }
}

// Handle 404 if route not found
if (!$found) {
    http_response_code(404);
    echo json_encode(['message' => 'Invalid request']);
    exit;
}
