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
use Controllers\HumanResourceController;

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
$humanResourceController = new HumanResourceController();

// Define routes
$routes = [
    'GET' => [
        '/' => function () {
            echo json_encode(['status' => 'ok']);
            exit;
        },
        '/auth/logout' => [$authController, 'logout'],
        '/roles' => [$authController, 'getRoles'],
        '/permissions' => [$authController, 'getPermissions'],
        '/currencies' => [$productController, 'getCurrencies'],
        '/payment_methods' => [$productController, 'getPaymentMethods'],
        '.payment_terms' => [$productController, 'getPaymentTerms'],
        '/departments' => [$productController, 'getDepartments'],
        '/item_categories' => [$productController, 'getItemCategories'],
        '/item_manufacturers' => [$productController, 'getItemManufacturers'],
        '/units' => [$productController, 'getUnits'],
        '/no_of_working_days' => [$productController, 'getNoOfWorkingDays'],
        '/branches' => [$productController, 'getBranches'],
        '/base-pay-types' => [$productController, 'getBasePayTypes'],
        '/vendors' => [$vendorController, 'index'],
        '/vendor_categories' => [$vendorController, 'getVendorCategories'],
        '/users' => [$adminController, 'getUsers'],
        '/admins' => [$humanResourceController, 'getAdmins'],
        '/admin/overview' => [$adminController, 'overview'],
        '/taxes' => [$productController, 'getTaxes'],
        '/customers' => [$customerController, 'index'],
        '/dashboard/business' => [$dashboardController, 'businessOverview'],
        '/dashboard/metrics' => [$productController, 'getDashboardMetrics'],
        '/dashboard/overview' => [$dashboardController, 'overview'],
        '/dashboard/products/lowstock' => [$dashboardController, 'lowQuantityStock'],
        '/dashboard/products/mostpurchased' => [$dashboardController, 'mostPurchased'],
        '/dashboard/products/topselling' => [$dashboardController, 'topSelling'],
        '/dashboard/inventory/tracker' => [$inventoryController, 'getInventoryTracker'],
        '/human-resources/departments' => [$humanResourceController, 'getDepartments'],
        '/human-resources/employees' => [$humanResourceController, 'getEmployees'],
        '/human-resources/overview' => [$humanResourceController, 'overview'],
        '/purchases/orders' => [$tradeController, 'purchaseIndex'],
        '/sales/orders' => [$tradeController, 'saleIndex'],
        '/sales/price-list' => [$tradeController, 'getpriceList'],
        '/inventory' => [$inventoryController, 'index'],
        '/vendors/(\d+)' => [$vendorController, 'show'],
        '/customers/(\d+)' => [$customerController, 'show'],
        '/products/(\d+)' => [$productController, 'show'],
        '/human-resources/employees/(\d+)' => [$humanResourceController, 'showEmployee'],
        '/purchases/orders/(\d+)' => [$tradeController, 'showPurchase'],
        '/inventory/history/(\d+)' => [$inventoryController, 'inventoryHistory'],
        '/inventory/items/(\d+)' => [$inventoryController, 'showItem'],
        '/purchases/orders/invoice/(\d+)' => [$tradeController, 'getPurchaseInvoice'],
        '/sales/orders/invoice/(\d+)' => [$tradeController, 'getSalesInvoice'],
    ],
    'POST' => [
        '/auth/register' => [$authController, 'register'],
        '/auth/login' => [$authController, 'login'],
        '/products' => [$productController, 'create'],
        '/customers' => [$customerController, 'create'],
        '/vendors' => [$vendorController, 'create'],
        '/admin/create' => [$adminController, 'createAdmin'],
        '/human-resources/departments' => [$humanResourceController, 'createDepartment'],
        '/human-resources/employees' => [$humanResourceController, 'createEmployee'],
        '/purchases/orders' => [$tradeController, 'createPurchase'],
        '/inventory/items' => [$inventoryController, 'createItem'],
        '/inventory/completed' => [$inventoryController, 'completeInventory'],
        '/admin/register' => [$adminController, 'registerAdmin'],
        '/employees/register' => [$employeeController, 'create'],
        '/sales/orders' => [$tradeController, 'createSale'],
        '/sales/price-list' => [$tradeController, 'createPriceList'],
        '/human-resources/employees/leave/apply' => [$humanResourceController, 'applyLeave'],
        '/human-resources/employees/(\d+)/suspend' => [$humanResourceController, 'suspendEmployee'],
        '/purchases/orders/received/(\d+)' => [$tradeController, 'markPurchaseAsReceived'],
        '/human-resources/employees/leave/(\d+)/approve' => [$humanResourceController, 'approveLeave'],
        '/inventory/items/(\d+)' => [$inventoryController, 'updateItem'],
        '/inventory/items/stocks/(\d+)' => [$inventoryController, 'adjustStock'],
    ],
    'PUT' => [
        '/products/quantity/(\d+)' => [$productController, 'updateQuantity'],
        '/sales/price-list/(\d+)' => [$tradeController, 'updatePriceList'],
        '/products/(\d+)' => [$productController, 'update'],
        '/customers/(\d+)' => [$customerController, 'update'],
        '/vendors/(\d+)' => [$vendorController, 'update'],
    ],
    'DELETE' => [
        '/sales/price-list/(\d+)' => [$tradeController, 'deletePriceList'],
        '/human-resources/employees/(\d+)' => [$humanResourceController, 'deleteEmployee'],
        '/products/(\d+)' => [$productController, 'delete'],
        '/employees/(\d+)' => [$employeeController, 'delete'],
        '/customers/(\d+)' => [$customerController, 'delete'],
        '/vendors/(\d+)' => [$vendorController, 'delete'],
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
