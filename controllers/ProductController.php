<?php

namespace Controllers;

use Models\Product;
use Services\MediaHandler;

class ProductController extends BaseController
{
    private $product;
    private $mediaHandler;

    public function __construct()
    {
        parent::__construct();
        $this->product = new Product();
        $this->mediaHandler = new MediaHandler();
    }

    public function index()
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $pageSize = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter['search'] = $search;

        $result = $this->product->fetchProducts($page, $pageSize, false, $filter);

        $this->sendResponse('Success', 200, $result['products'], $result['meta']);
    }

    public function show($id)
    {
        $product = $this->product->fetchProduct($id);
        if ($product) {
            $this->sendResponse('Success', 200, $product);
        } else {
            $this->sendResponse('Product not found', 404);
        }
    }

    public function create()
    {
        $data = $this->getRequestData();

        $formData = $data['form_data'];
        $formData['storage'] = 1; // Cold Room
        $mediaFiles = $data['files']['media'] ?? [];

        $requiredFields = [
            'name', 'location', 'vendor', 'code',
            'sku', 'barcode', 'price', 'quantity', 'unit'
        ];

        $dataToValidate = array_intersect_key($formData, array_flip($requiredFields));

        if (!$this->validateFields(...array_values($dataToValidate))) {
            $this->sendResponse('Invalid input data', 400);
        }

        // Handle media files using MediaHandler
        if (!empty($mediaFiles)) {
            $mediaLinks = $this->mediaHandler->handleMediaFiles($mediaFiles);

            if ($mediaLinks === false) {
                $this->sendResponse('Error uploading media files', 500);
            }

        }

        $result = $this->product->create($formData, $mediaLinks);

        if ($result) {
            $this->sendResponse('Success', 201, ['product_id' => $result]);
        } else {
            $this->sendResponse('Failed to create product', 500);
        }
    }

    public function getDashboardMetrics()
    {
        $this->authorizeRequest();
        echo json_encode([
            'message' => 'Success',
            'data' => [
                'total_items' => $this->product->countUnits([]),
                'low_stock_alerts' => $this->product->countUnits(['low_stock' => true]),
                'total_to_be_delivered' => $this->product->countUnits(['to_be_delivered' => true]),
                'total_to_be_ordered' => $this->product->countUnits(['to_be_ordered' => true]),
            ]
        ]);
    }

    public function getWarehouseDetailsMetrics()
    {
        echo json_encode([
            'message' => 'Success',
            'data' => [
                'total_items' => $this->product->countUnits([]),
                'cold_room' => $this->product->countUnits(['storage_id' => 1]),
                'kitchen' => $this->product->countUnits(['storage_id' => 2]),
            ]
        ]);
    }

    public function getTopSellingProducts()
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $pageSize = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

        $result = $this->product->fetchProducts($page, $pageSize, true);

        $this->sendResponse('Success', 200, $result['products'], $result['meta']);
    }

    public function getVendors()
    {
        $result = $this->fetchVendors();
        $this->sendResponse('Success', 200, $result);

    }

    public function getUnits()
    {
        $result = $this->fetchUnits();
        $this->sendResponse('Success', 200, $result);
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
