<?php

namespace Controllers;

use Models\Product;

class ProductController
{
    private $product;

    public function __construct()
    {
        $this->product = new Product();
    }




    public function create()
    {
        // Get raw JSON input data
        $data = json_decode(file_get_contents('php://input'), true);




        // Check if data is an array of multiple products or a single product
        if (is_array($data)) {
            // Check if the array contains multiple objects (array of products)
            if (isset($data[0]) && is_array($data[0])) {
                // Handle multiple products
                foreach ($data as $product) {
                    // Extract data for each product
                    $name = $product['name'] ?? null;
                    $location = $product['location'] ?? null;
                    $vendor = $product['vendor'] ?? null;
                    $code = $product['code'] ?? null;
                    $sku = $product['sku'] ?? null;
                    $barcode = $product['barcode'] ?? null;
                    $price = $product['price'] ?? null;
                    $quantity = $product['quantity'] ?? null;
                    $unit = $product['unit'] ?? null;
                    $media_path = $product['media_path'] ?? null;

                    // Validate required fields
                    if (empty($name) || empty($location) || empty($vendor) || empty($code) || empty($sku) || empty($barcode) || empty($price) || empty($quantity) || empty($unit) || empty($media_path)) {
                        echo json_encode(['message' => 'All fields are required for each product']);
                        return;
                    }

                    // Call the create method in the Product model
                    $this->product->create($name, $location, $vendor, $code, $sku, $barcode, $price, $quantity, $unit, $media_path);
                }
                echo json_encode(['message' => 'All products created successfully']);
            } else {
                // Handle a single product (when it's just a single object, not an array of products)
                $name = $data['name'] ?? null;
                $location = $data['location'] ?? null;
                $vendor = $data['vendor'] ?? null;
                $code = $data['code'] ?? null;
                $sku = $data['sku'] ?? null;
                $barcode = $data['barcode'] ?? null;
                $price = $data['price'] ?? null;
                $quantity = $data['quantity'] ?? null;
                $unit = $data['unit'] ?? null;
                $media_path = $data['media_path'] ?? null;

                // Validate required fields
                if (empty($name) || empty($location) || empty($vendor) || empty($code) || empty($sku) || empty($barcode) || empty($price) || empty($quantity) || empty($unit) || empty($media_path)) {
                    echo json_encode(['message' => 'All fields are required']);
                    return;
                }

                // Call the create method in the Product model
                $this->product->create($name, $location, $vendor, $code, $sku, $barcode, $price, $quantity, $unit, $media_path);

                echo json_encode(['message' => 'Product created successfully']);
            }
        } else {
            echo json_encode(['message' => 'Invalid input data']);
        }
    }




    // Get All Products
    public function getAll()
    {
        echo json_encode($this->product->getAll());
    }

    // Get Single Product
    public function get($id)
    {
        echo json_encode($this->product->get($id));
    }

    //Get Total number of products
    public function getTotalItems()
    {
        echo json_encode($this->product->getTotalItems());
    }

    //Get Total number of Low stock products

    public function getLowStockAlerts()
    {
        echo json_encode($this->product->getLowStockAlerts());
    }
    //Get Total number of warehouse products
    public function getWhNo()
    {
        echo json_encode($this->product->getWhNo());
    }
    public function getWhItems()
    {
        echo json_encode($this->product->getWhItems());
    }
    //Get all products in warehouse A
    public function getWhA()
    {
        echo json_encode($this->product->getWhA());
    }

    //Get all products in warehouse B
    public function getLowStockAlertsA()
    {
        echo json_encode($this->product->getLowStockAlertsA());
    }

    public function getWhB()
    {
        echo json_encode($this->product->getWhB());
    }
    // Update Product
    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->product->update($id, $data['name'], $data['location'], $data['vendor'], $data['code'], $data['price'], $data['profit'], $data['margin'], $data['quantity'], $data['unit'], $data['image_path'])) {
            echo json_encode(['message' => 'Product updated successfully']);
        } else {
            echo json_encode(['message' => 'Failed to update product']);
        }
    }

    // Delete Product
    public function delete($id)
    {
        if ($this->product->delete($id)) {
            echo json_encode(['message' => 'Product deleted successfully']);
        } else {
            echo json_encode(['message' => 'Failed to delete product']);
        }
    }


}
