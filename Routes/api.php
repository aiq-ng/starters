<?php
require_once __DIR__ . '/../Controllers/auth.php';
require_once __DIR__ . '/../Controllers/productscontroller.php';



$authController = new AuthController();
$productController = new ProductsController();


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
}

else {
    echo json_encode(['message' => 'Invalid Request']);
}

