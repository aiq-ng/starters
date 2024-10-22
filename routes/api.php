<?php

use Controllers\AuthController;
use Controllers\ProductController;
use Controllers\WarehouseController;
use Controllers\TradeController;
use Controllers\InventoryController;

// Create instances of the controllers
$authController = new AuthController();
$productController = new ProductController();
$warehouseController = new WarehouseController();
$tradeController = new TradeController();
$inventoryController = new InventoryController();

// Define routes
$routes = [
    'GET' => [
        '/' => function () {
            echo json_encode(['status' => 'ok']);
            exit;
        },
        '/auth/logout' => [$authController, 'logout'],
        '/roles' => [$authController, 'getRoles'],
        '/products' => [$productController, 'index'],
        '/warehouses' => [$warehouseController, 'index'],
        '/vendors' => [$productController, 'getVendors'],
        '/suppliers' => [$productController, 'getSuppliers'],
        '/units' => [$productController, 'getUnits'],
        '/dashboard/metrics' => [$productController, 'getDashboardMetrics'],
        '/dashboard/warehouses/details' => [$productController, 'getWarehouseDetailsMetrics'],
        '/dashboard/products/topselling' => [$productController, 'getTopSellingProducts'],
        '/purchases' => [$tradeController, 'purchaseIndex'],
        '/sales' => [$tradeController, 'saleIndex'],
        '/products/(\d+)' => [$productController, 'show'],
        '/inventory' => [$inventoryController, 'getInventory'],
        '/inventory/plans' => [$inventoryController, 'index'],
        '/inventory/status' => [$inventoryController, 'getInventoryByStatus'],
        '/inventory/:id' => [$inventoryController, 'getInventoryPlan'],
        '/inventory/stock' => [$inventoryController, 'getStockProgress'],



    ],
    'POST' => [
        '/auth/register' => [$authController, 'register'],
        '/auth/login' => [$authController, 'login'],
        '/products' => [$productController, 'create'],
        '/warehouses' => [$warehouseController, 'create'],
        '/purchases' => [$tradeController, 'createPurchase'],
        '/sales' => [$tradeController, 'createSale'],
        '/inventory/plans' => [$inventoryController, 'create'],
    ],
    'PUT' => [
        '/products/(\d+)' => [$productController, 'update'],
        '/products/quantity/(\d+)' => [$productController, 'updateQuantity'],
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
