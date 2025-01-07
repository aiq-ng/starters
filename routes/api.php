<?php

use Controllers\AuthController;
use Controllers\CustomerController;
use Controllers\TradeController;
use Controllers\InventoryController;
use Controllers\VendorController;
use Controllers\DashboardController;
use Controllers\AdminController;
use Controllers\HumanResourceController;
use Controllers\AccountingController;

// Create instances of the controllers
$authController = new AuthController();
$customerController = new CustomerController();
$vendorController = new VendorController();
$tradeController = new TradeController();
$inventoryController = new InventoryController();
$dashboardController = new DashboardController();
$adminController = new AdminController();
$humanResourceController = new HumanResourceController();
$accountingController = new AccountingController();

// Define routes
$routes = [
    'GET' => [
        '/' => function () {
            echo json_encode(['status' => 'ok']);
            exit;
        },
        '/roles' => [$authController, 'getRoles'],
        '/permissions' => [$authController, 'getPermissions'],
        '/currencies' => [$adminController, 'getCurrencies'],
        '/payment_methods' => [$adminController, 'getPaymentMethods'],
        '.payment_terms' => [$adminController, 'getPaymentTerms'],
        '/departments' => [$adminController, 'getDepartments'],
        '/item_categories' => [$adminController, 'getItemCategories'],
        '/expenses_categories' => [$adminController, 'getExpensesCategories'],
        '/item_manufacturers' => [$adminController, 'getItemManufacturers'],
        '/units' => [$adminController, 'getUnits'],
        '/no_of_working_days' => [$adminController, 'getNoOfWorkingDays'],
        '/branches' => [$adminController, 'getBranches'],
        '/base-pay-types' => [$adminController, 'getBasePayTypes'],
        '/vendors' => [$vendorController, 'index'],
        '/vendor_categories' => [$vendorController, 'getVendorCategories'],
        '/users' => [$adminController, 'getUsers'],
        '/admins' => [$humanResourceController, 'getAdmins'],
        '/admin/overview' => [$adminController, 'overview'],
        '/taxes' => [$adminController, 'getTaxes'],
        '/customers' => [$customerController, 'index'],
        '/work_leave_qualifications' => [$humanResourceController, 'getWorkLeaveQualifications'],
        '/dashboard/business' => [$dashboardController, 'businessOverview'],
        '/dashboard/metrics' => [$adminController, 'getDashboardMetrics'],
        '/dashboard/overview' => [$dashboardController, 'overview'],
        '/dashboard/products/lowstock' => [$dashboardController, 'lowQuantityStock'],
        '/dashboard/products/mostpurchased' => [$dashboardController, 'mostPurchased'],
        '/dashboard/products/topselling' => [$dashboardController, 'topSelling'],
        '/dashboard/inventory/tracker' => [$inventoryController, 'getInventoryTracker'],
        '/dashboard/cashflow' => [$dashboardController, 'cashflow'],
        '/human-resources/departments' => [$humanResourceController, 'getDepartments'],
        '/human-resources/employees' => [$humanResourceController, 'getEmployees'],
        '/human-resources/overview' => [$humanResourceController, 'overview'],
        '/purchases/orders' => [$tradeController, 'purchaseIndex'],
        '/sales/orders' => [$tradeController, 'saleIndex'],
        '/sales/price-list' => [$tradeController, 'getpriceList'],
        '/sales/overview' => [$tradeController, 'salesOverview'],
        '/sales/graph' => [$tradeController, 'salesGraph'],
        '/sales/stocks/topselling' => [$tradeController, 'topSellingStocks'],
        '/sales/upcoming-events' => [$tradeController, 'upcomingEvents'],
        '/accounting/expenses' => [$accountingController, 'getExpenses'],
        '/accounting/bills' => [$accountingController, 'getBills'],
        '/accounting/overview' => [$accountingController, 'overview'],
        '/accounting/graph' => [$accountingController, 'revenueAndExpensesGraph'],
        '/inventory' => [$inventoryController, 'index'],
        '/notifications/(\d+|[a-fA-F0-9-]{36})' => [$adminController, 'getNotifications'],
        '/vendors/(\d+|[a-fA-F0-9-]{36})' => [$vendorController, 'show'],
        '/customers/(\d+|[a-fA-F0-9-]{36})' => [$customerController, 'show'],
        '/products/(\d+|[a-fA-F0-9-]{36})' => [$adminController, 'show'],
        '/sales/price-list/(\d+|[a-fA-F0-9-]{36})' => [$tradeController, 'getAPriceList'],
        '/accounting/sales-orders/(\d+|[a-fA-F0-9-]{36})' => [$accountingController, 'getSalesOrder'],
        '/human-resources/employees/(\d+|[a-fA-F0-9-]{36})' => [$humanResourceController, 'showEmployee'],
        '/purchases/orders/(\d+|[a-fA-F0-9-]{36})' => [$tradeController, 'showPurchase'],
        '/inventory/history/(\d+|[a-fA-F0-9-]{36})' => [$inventoryController, 'inventoryHistory'],
        '/inventory/items/(\d+|[a-fA-F0-9-]{36})' => [$inventoryController, 'showItem'],
        '/purchases/orders/invoice/(\d+|[a-fA-F0-9-]{36})' => [$tradeController, 'getPurchaseInvoice'],
        '/sales/orders/invoice/(\d+|[a-fA-F0-9-]{36})' => [$tradeController, 'getSalesInvoice'],
    ],
    'POST' => [
        '/auth/register' => [$authController, 'register'],
        '/auth/login' => [$authController, 'login'],
        '/auth/refresh' => [$authController, 'refresh'],
        '/auth/logout' => [$authController, 'logout'],
        '/sessions/invalidate' => [$authController, 'invalidateSessions'],
        '/products' => [$adminController, 'create'],
        '/customers' => [$customerController, 'create'],
        '/vendors' => [$vendorController, 'create'],
        '/roles' => [$authController, 'createRole'],
        '/units' => [$adminController, 'createUnit'],
        '/currencies' => [$adminController, 'createCurrency'],
        '/taxes' => [$adminController, 'createTax'],
        '/permissions' => [$authController, 'createPermission'],
        '/payment_methods' => [$adminController, 'createPaymentMethod'],
        '/payment_terms' => [$adminController, 'createPaymentTerm'],
        '/item_categories' => [$adminController, 'createItemCategory'],
        '/expenses_categories' => [$adminController, 'createExpensesCategory'],
        '/item_manufacturers' => [$adminController, 'createItemManufacturer'],
        '/no_of_working_days' => [$adminController, 'createNoOfWorkingDays'],
        '/branches' => [$adminController, 'createBranch'],
        '/base-pay-types' => [$adminController, 'createBasePayType'],
        '/vendor_categories' => [$vendorController, 'createVendorCategory'],
        '/admin/create' => [$adminController, 'createAdmin'],
        '/human-resources/departments' => [$humanResourceController, 'createDepartment'],
        '/human-resources/employees' => [$humanResourceController, 'createEmployee'],
        '/work_leave_qualifications' => [$humanResourceController, 'createWorkLeaveQualification'],
        '/purchases/orders' => [$tradeController, 'createPurchase'],
        '/inventory/items' => [$inventoryController, 'createItem'],
        '/inventory/completed' => [$inventoryController, 'completeInventory'],
        '/admin/register' => [$adminController, 'registerAdmin'],
        '/employees/register' => [$humanResourceController, 'create'],
        '/sales/orders' => [$tradeController, 'createSale'],
        '/sales/price-list' => [$tradeController, 'createPriceList'],
        '/accounting/expenses' => [$accountingController, 'createExpense'],
        '/send-notification' => [$adminController, 'sendNotification'],
        '/human-resources/employees/leave/apply' => [$humanResourceController, 'applyLeave'],
        '/human-resources/employees/(\d+|[a-fA-F0-9-]{36})/suspend' => [$humanResourceController, 'suspendEmployee'],
        '/purchases/orders/received/(\d+|[a-fA-F0-9-]{36})' => [$tradeController, 'markPurchaseAsReceived'],
        '/accounting/sales-orders/(\d+|[a-fA-F0-9-]{36})/confirm-payment' => [$accountingController, 'comfirmSalesOrderPayment'],
        '/human-resources/employees/leave/(\d+|[a-fA-F0-9-]{36})/approve' => [$humanResourceController, 'approveLeave'],
        '/inventory/items/(\d+|[a-fA-F0-9-]{36})' => [$inventoryController, 'updateItem'],
        '/inventory/items/stocks/(\d+|[a-fA-F0-9-]{36})' => [$inventoryController, 'adjustStock'],
        '/sales/orders/invoice/(\d+|[a-fA-F0-9-]{36})' => [$tradeController, 'sendSaleInvoice'],
        '/purchases/orders/invoice/(\d+|[a-fA-F0-9-]{36})' => [$tradeController, 'sendPurchaseInvoice'],

    ],
    'PUT' => [
        '/products/quantity/(\d+|[a-fA-F0-9-]{36})' => [$adminController, 'updateQuantity'],
        '/sales/price-list/(\d+|[a-fA-F0-9-]{36})' => [$tradeController, 'updatePriceList'],
        '/products/(\d+|[a-fA-F0-9-]{36})' => [$adminController, 'update'],
        '/customers/(\d+|[a-fA-F0-9-]{36})' => [$customerController, 'update'],
        '/vendors/(\d+|[a-fA-F0-9-]{36})' => [$vendorController, 'update'],
    ],
    'DELETE' => [
        '/sales/price-list/(\d+|[a-fA-F0-9-]{36})' => [$tradeController, 'deletePriceList'],
        '/human-resources/employees/(\d+|[a-fA-F0-9-]{36})' => [$humanResourceController, 'deleteEmployee'],
        '/products/(\d+|[a-fA-F0-9-]{36})' => [$adminController, 'delete'],
        '/employees/(\d+|[a-fA-F0-9-]{36})' => [$humanResourceController, 'delete'],
        '/customers/(\d+|[a-fA-F0-9-]{36})' => [$customerController, 'delete'],
        '/vendors/(\d+|[a-fA-F0-9-]{36})' => [$vendorController, 'delete'],
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
