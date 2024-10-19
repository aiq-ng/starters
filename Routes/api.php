<?php

require_once __DIR__ . '/../Controllers/auth.php';
require_once __DIR__ . '/../Controllers/productscontroller.php';
require_once __DIR__ . './../Controllers/warehousecontroller.php';



$authController = new AuthController();
$productController = new ProductsController();
$warehouseController = new WarehouseController();

// Health check route
if ($_SERVER['REQUEST_URI'] === '/') {
    echo json_encode(['status' => 'ok']);
    exit;
}

if ($_SERVER['REQUEST_URI'] === '/auth/register') {
    $authController->register();
} elseif ($_SERVER['REQUEST_URI'] === '/auth/login') {
    $authController->login();
} elseif ($_SERVER['REQUEST_URI'] === '/auth/logout') {
    $authController->logout();
}
// Route for creating a product
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/products') {
    $productController->create();
}

// Route for getting all products
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/products') {
    $productController->getAll();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/products/1') {
    $productController->get($id);
}
// Route for getting total number of products
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/products/noofproducts') {
    $productController->getTotalItems();
}

// Route for getting low stock alert products
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/products/lowstockalerts') {
    $productController->getLowStockAlerts();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/warehouse') {
    $warehouseController->createWh();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/products/warehouseno') {
    $productController->getWhNo();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/products/warehouseitems') {
    $productController->getWhItems();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/products/warehousea') {
    $productController->getWhA();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/products/warehouseb') {
    $productController->getWhB();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/products/lowstockalertsa') {
    $productController->getLowStockAlertsA();
}
// Route for getting a single product
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('/\/products\/(\d+)/', $_SERVER['REQUEST_URI'], $matches)) {
    $id = $matches[1];
    $productController->get($id);
}

// Route for updating a product
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT' && preg_match('/\/products\/(\d+)/', $_SERVER['REQUEST_URI'], $matches)) {
    $id = $matches[1];
    $productController->update($id);
}

// Route for deleting a product
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && preg_match('/\/products\/(\d+)/', $_SERVER['REQUEST_URI'], $matches)) {
    $id = $matches[1];
    $productController->delete($id);
} else {
    echo json_encode(['message' => 'Invalid Request']);
}
