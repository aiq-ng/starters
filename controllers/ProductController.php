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
        $this->authorizeRequest();

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $pageSize = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $filter['search'] = $search;
        $filter['warehouse_id'] = isset($_GET['warehouse_id']) ? (int)$_GET['warehouse_id'] : null;

        $result = $this->product->fetchProducts($page, $pageSize, false, $filter);

        $this->sendResponse('Success', 200, $result['products'], $result['meta']);
    }

    public function show($id)
    {
        $this->authorizeRequest();

        $product = $this->product->fetchProduct($id);
        if ($product) {
            $this->sendResponse('Success', 200, $product);
        } else {
            $this->sendResponse('Product not found', 404);
        }
    }

    public function create()
    {
        $this->authorizeRequest();

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
        $this->authorizeRequest();

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
        $this->authorizeRequest();

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $pageSize = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

        $result = $this->product->fetchProducts($page, $pageSize, true);

        $this->sendResponse('Success', 200, $result['products'], $result['meta']);
    }

    public function getVendors()
    {
        $this->authorizeRequest();

        $result = $this->fetchVendors();
        $this->sendResponse('Success', 200, $result);

    }

    public function getUnits()
    {
        $this->authorizeRequest();

        $result = $this->fetchUnits();
        $this->sendResponse('Success', 200, $result);
    }

    public function updateQuantity($id)
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();
        $data['user_id'] = $_SESSION['user_id'];

        if (!$this->validateFields($data['new_quantity'], $data['reason'])) {
            $this->sendResponse('Invalid input data', 400);
        }

        $result = $this->product->updateProductQuantity($id, $data);

        if ($result) {
            $this->sendResponse('Success', 200, $result);
        } else {
            $this->sendResponse('Failed to update product quantity', 500);
        }
    }


}
