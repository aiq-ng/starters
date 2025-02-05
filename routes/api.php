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
use Controllers\KitchenController;

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
$kitchenController = new KitchenController();

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
        '/chefs' => [$kitchenController, 'getChefs'],
        '/notifications/' => [$adminController, 'getNotifications'],
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
        '/kitchen' => [$kitchenController, 'index'],
        '/accounting/expenses' => [$accountingController, 'getExpenses'],
        '/accounting/bills' => [$accountingController, 'getBills'],
        '/accounting/overview' => [$accountingController, 'overview'],
        '/accounting/graph' => [$accountingController, 'revenueAndExpensesGraph'],
        '/inventory' => [$inventoryController, 'index'],
        '/kitchen/chef/orders' => [$kitchenController, 'getChefOrders'],
        '/kitchen/orders/([a-fA-F0-9-]{36})' => [$kitchenController, 'show'],
        '/inventory/graph/([a-fA-F0-9-]{36})' => [$inventoryController, 'graph'],
        '/vendors/([a-fA-F0-9-]{36})' => [$vendorController, 'show'],
        '/customers/([a-fA-F0-9-]{36})' => [$customerController, 'show'],
        '/products/([a-fA-F0-9-]{36})' => [$adminController, 'show'],
        '/sales/price-list/([a-fA-F0-9-]{36})' => [$tradeController, 'getAPriceList'],
        '/accounting/sales-orders/([a-fA-F0-9-]{36})' => [$accountingController, 'getSalesOrder'],
        '/accounting/expenses/([a-fA-F0-9-]{36})' => [$accountingController, 'getExpense'],
        '/human-resources/employees/([a-fA-F0-9-]{36})' => [$humanResourceController, 'showEmployee'],
        '/purchases/orders/([a-fA-F0-9-]{36})' => [$tradeController, 'showPurchase'],
        '/inventory/history/([a-fA-F0-9-]{36})' => [$inventoryController, 'inventoryHistory'],
        '/inventory/items/([a-fA-F0-9-]{36})' => [$inventoryController, 'showItem'],
        '/purchases/orders/invoice/([a-fA-F0-9-]{36})' => [$tradeController, 'getPurchaseInvoice'],
        '/sales/orders/invoice/([a-fA-F0-9-]{36})' => [$tradeController, 'getSalesInvoice'],
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
        '/accounting/received' => [$accountingController, 'markAsReceived'],
        '/accounting/expenses' => [$accountingController, 'createExpense'],
        '/send-notification' => [$adminController, 'sendNotification'],
        '/human-resources/employees/leave/apply' => [$humanResourceController, 'applyLeave'],
        '/kitchen/order/status/([a-fA-F0-9-]{36})' => [$kitchenController, 'updateStatus'],
        '/kitchen/order/assign/([a-fA-F0-9-]{36})' => [$kitchenController, 'assignOrder'],
        '/human-resources/employees/([a-fA-F0-9-]{36})/suspend' => [$humanResourceController, 'suspendEmployee'],
        '/purchases/orders/received/([a-fA-F0-9-]{36})' => [$tradeController, 'markPurchaseAsReceived'],
        '/accounting/sales-orders/([a-fA-F0-9-]{36})/confirm-payment' => [$accountingController, 'comfirmSalesOrderPayment'],
        '/human-resources/employees/leave/([a-fA-F0-9-]{36})/approve' => [$humanResourceController, 'approveLeave'],
        '/inventory/items/stocks/([a-fA-F0-9-]{36})' => [$inventoryController, 'adjustStock'],
        '/inventory/history/comment/([a-fA-F0-9-]{36})' => [$inventoryController, 'comment'],
        '/sales/orders/invoice/([a-fA-F0-9-]{36})' => [$tradeController, 'sendSaleInvoice'],
        '/purchases/orders/invoice/([a-fA-F0-9-]{36})' => [$tradeController, 'sendPurchaseInvoice'],

    ],
    'PUT' => [
        '/products/quantity/([a-fA-F0-9-]{36})' => [$adminController, 'updateQuantity'],
        '/sales/price-list/([a-fA-F0-9-]{36})' => [$tradeController, 'updatePriceList'],
        '/products/([a-fA-F0-9-]{36})' => [$adminController, 'update'],
        '/customers/([a-fA-F0-9-]{36})' => [$customerController, 'update'],
        '/vendors/([a-fA-F0-9-]{36})' => [$vendorController, 'update'],
        '/purchases/orders/([a-fA-F0-9-]{36})' => [$tradeController, 'updatePurchase'],
        '/sales/orders/([a-fA-F0-9-]{36})' => [$tradeController, 'updateSales'],
        '/inventory/items/([a-fA-F0-9-]{36})' => [$inventoryController, 'updateItem'],
    ],
    'PATCH' => [
        '/sales/orders/([a-fA-F0-9-]{36})' => [$tradeController, 'patchSale'],
    ],
    'DELETE' => [
        '/purchases/orders' => [$tradeController, 'deletePurchaseOrder'],
        '/sales/orders' => [$tradeController, 'deleteSalesOrder'],
        '/sales/price-list' => [$tradeController, 'deletePriceList'],
        '/customers' => [$customerController, 'delete'],
        '/vendors' => [$vendorController, 'delete'],
        '/inventory/items' => [$inventoryController, 'deleteItem'],
        '/accounting/expenses' => [$accountingController, 'deleteExpense'],
        '/human-resources/employees' => [$humanResourceController, 'deleteEmployee'],
        '/products/([a-fA-F0-9-]{36})' => [$adminController, 'delete'],
        '/employees/([a-fA-F0-9-]{36})' => [$humanResourceController, 'delete'],
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
