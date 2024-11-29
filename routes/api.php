<?php

use Controllers\AuthController;
use Controllers\ProductController;
use Controllers\CustomerController;
use Controllers\TradeController;
use Controllers\InventoryController;
use Controllers\VendorController;
use Controllers\DashboardController;
use Controllers\AdminController;
use Controllers\EmployeeController;

// Create instances of the controllers
$authController = new AuthController();
$productController = new ProductController();
$customerController = new CustomerController();
$vendorController = new VendorController();
$tradeController = new TradeController();
$inventoryController = new InventoryController();
$dashboardController = new DashboardController();
$adminController = new AdminController();
$employeeController = new EmployeeController();

// Define routes
$routes = [
    'GET' => [
        '/' => function () {
            echo json_encode(['status' => 'ok']);
            exit;
        },
        '/auth/logout' => [$authController, 'logout'],
        '/roles' => [$authController, 'getRoles'],
        '/currencies' => [$productController, 'getCurrencies'],
        '/payment_methods' => [$productController, 'getPaymentMethods'],
        '/departments' => [$productController, 'getDepartments'],
        '/item_categories' => [$productController, 'getItemCategories'],
        '/item_manufacturers' => [$productController, 'getItemManufacturers'],
        '/units' => [$productController, 'getUnits'],
        '/vendors' => [$vendorController, 'index'],
        '/customers' => [$customerController, 'index'],
        '/dashboard/business' => [$dashboardController, 'businessOverview'],
        '/dashboard/metrics' => [$productController, 'getDashboardMetrics'],
        '/dashboard/overview' => [$dashboardController, 'overview'],
        '/dashboard/products/lowstock' => [$dashboardController, 'lowQuantityStock'],
        '/dashboard/products/mostpurchased' => [$dashboardController, 'mostPurchased'],
        '/dashboard/products/topselling' => [$dashboardController, 'topSelling'],
        '/dashboard/inventory/tracker' => [$inventoryController, 'getInventoryTracker'],
        '/purchases/orders' => [$tradeController, 'purchaseIndex'],
        '/sales/orders' => [$tradeController, 'saleIndex'],
        '/products/(\d+)' => [$productController, 'show'],
        '/inventory' => [$inventoryController, 'index'],
        '/inventory/items/(\d+)' => [$inventoryController, 'showItem'],
        '/inventory/tracker' => [$inventoryController, 'inventoryTracker'],
        '/inventory/:id' => [$inventoryController, 'getInventoryPlan'],
        '/inventory/stock' => [$inventoryController, 'getStockProgress'],
        '/admins/count' => [$adminController, 'numberOfAdmins'],
        '/employees' => [$employeeController, 'index'],
        'employees/:id' => [$employeeController, 'show']



    ],
    'POST' => [
        '/auth/register' => [$authController, 'register'],
        '/auth/login' => [$authController, 'login'],
        '/products' => [$productController, 'create'],
        '/customers' => [$customerController, 'create'],
        '/vendors' => [$vendorController, 'create'],
        '/purchases/orders' => [$tradeController, 'createPurchase'],
        '/sales/orders' => [$tradeController, 'createSale'],
        '/inventory/items' => [$inventoryController, 'createItem'],
        '/inventory/completed' => [$inventoryController, 'completeInventory'],
        '/admin/register' => [$adminController, 'registerAdmin'],
        '/employees/register' => [$employeeController, 'create'],
        '/inventory/items/(\d+)' => [$inventoryController, 'updateItem'],
    ],
    'PUT' => [
        '/products/(\d+)' => [$productController, 'update'],
        '/products/quantity/(\d+)' => [$productController, 'updateQuantity'],
    ],
    'DELETE' => [
        '/products/(\d+)' => [$productController, 'delete'],
        '/employees/(\d+)' => [$employeeController, 'delete']
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
