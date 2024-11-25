<?php

namespace Controllers;

use Models\Inventory;
use Services\MediaHandler;

class InventoryController extends BaseController
{
    private $inventory;
    private $mediaHandler;


    public function __construct()
    {
        parent::__construct();
        $this->inventory = new Inventory();
        $this->mediaHandler = new MediaHandler();
    }


    public function index()
    {
        $this->authorizeRequest();
        $params = [
            'availability' => isset($_GET['availability']) ? $_GET['availability'] : null,
            'page' => isset($_GET['page']) ? (int)$_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? (int)$_GET['page_size'] : 10,
            'order' => isset($_GET['order']) ? $_GET['order'] : null,
            'sort' => isset($_GET['sort']) ? $_GET['sort'] : null,
        ];

        error_log(json_encode($params));

        $inventory = $this->inventory->getInventory($params);

        if (empty($inventory)) {
            $this->sendResponse('Inventory not found', 404, []);
        }
        $this->sendResponse('success', 200, $inventory['inventory'], $inventory['meta']);
    }

    public function createItem()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        $formData = $data['form_data'];
        $mediaFiles = $data['files']['media'] ?? [];

        $requiredFields = [
            'name', 'department_id', 'category_id', 'manufacturer_id',
            'expiry_date', 'quantity', 'unit_id'
        ];

        error_log(json_encode($formData));

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

        $result = $this->inventory->createItem($formData, $mediaLinks);

        if ($result) {
            $this->sendResponse('Success', 201, ['item_id' => $result]);
        } else {
            $this->sendResponse('Failed to create item', 500);
        }
    }


    public function show($id)
    {
        $this->authorizeRequest();
        $inventory = $this->inventory->getInventoryPlan($id);

        if (empty($inventory)) {
            $this->sendResponse('Inventory not found', 404, []);
        }
        $this->sendResponse('success', 200, $inventory);
    }

    public function create($data)
    {
        $this->authorizeRequest();
        $data = $this->getRequestData();
        if (!$this->validateFields(
            $data['name'],
            $data['products'],
            $data['plan_date']
        )) {
            $this->sendResponse('Missing required fields', 400);
        }

        if (!is_array($data['products'])) {
            $this->sendResponse('Products must be an array', 400);
        }

        error_log(json_encode($data));

        $planId = $this->inventory->saveInventoryPlan($data);

        if (!$planId) {
            $this->sendResponse('Failed to create Inventory Plan', 400);
        }
        $this->sendResponse('success', 200, ['plan_id' => $planId]);
    }

    public function getinventoryTracker()
    {
        $this->authorizeRequest();

        $inventory = $this->inventory->getInventoryTracker();

        if (empty($inventory)) {
            $this->sendResponse('Inventory not found', 404, []);
        }
        $this->sendResponse('success', 200, $inventory);
    }

    public function getWarehouseInventory($warehouseId)
    {
        $this->authorizeRequest();
        $params = [
            'page' => isset($_GET['page']) ? (int)$_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? (int)$_GET['page_size'] : 10,
        ];

        error_log(json_encode($params));

        $inventory = $this->inventory->getWarehouseInventory($warehouseId, $params);

        if (empty($inventory)) {
            $this->sendResponse('Inventory not found', 404, []);
        }
        $this->sendResponse('success', 200, $inventory['data'], $inventory['meta']);
    }

    public function completeInventory()
    {
        $this->authorizeRequest();
        $data = $this->getRequestData();

        if (!isset($data['products']) || !is_array($data['products'])) {
            $this->sendResponse('Invalid data format', 400);
            return;
        }

        error_log(json_encode($data['products']));


        $updated = $this->inventory->updateInventoryCount($data['products']);

        if (!$updated) {
            $this->sendResponse('Failed to update Inventory for some products', 400);
            return;
        }

        $this->sendResponse('Success', 200, $updated);
    }



    // Get Inventory Items Grouped by Status (e.g., Available, Depleting, KIV)
    public function getInventoryByStatus($status)
    {
        $plans = $this->inventory->getAllInventoryPlans();
        $filteredPlans = array_filter($plans, function ($plan) use ($status) {
            return strtolower($plan['status']) === strtolower($status);
        });

        if ($plans) {
            $this->sendResponse('success', 200, array_values($filteredPlans));
        } else {
            $this->sendResponse('Inventory plan not found', 400);
        }

    }





    public function getInventoryPlan($id)
    {
        $plan = $this->inventory->getInventoryPlan($id);
        if ($plan) {
            $this->sendResponse('success', 200, $plan);
        } else {
            $this->sendResponse('Inventory Plan not found', 400);
        }
    }

    // Update an Existing Inventory Plan
    public function updateInventoryPlan($id, $data)
    {
        $updated = $this->inventory->update($id, $data);
        if ($updated) {
            $this->sendResponse('success', 200, $updated);
        } else {
            $this->sendResponse('Failed to update Inventory Plan', 400);
        }
    }

    // Get Stock Levels and Progress for a Warehouse
    public function getStockProgress($warehouseId)
    {
        $inventory = $this->inventory->getInventoryByWarehouse($warehouseId);
        if ($inventory) {
            $this->sendResponse('success', 200, array_values($inventory));
        } else {
            $this->sendResponse('Inventory not found', 400);
        }
    }

    // Update Stock Levels and Progress Bar for a Product in a Warehouse
    public function updateStockProgress($warehouseId, $productId, $quantity, $progress)
    {
        $updated = $this->inventory->updateStock($warehouseId, $productId, $quantity, $progress);
        if ($updated) {
            $this->sendResponse('Stock updated successfully', 200, $updated);

        } else {
            $this->sendResponse('Failed to update stock', 400);

        }
    }

    //Search through Inventory plans
    public function searchInventory($keyword)
    {
        $plans = $this->inventory->getAllInventoryPlans();
        $filtered = array_filter($plans, function ($plan) use ($keyword) {
            return stripos($plan['name'], $keyword) !== false;
        });

        if ($filtered) {
            $this->sendResponse('Stock updated successfully', 200, array_values($filtered));

        } else {
            $this->sendResponse('Failed to update stock', 400);

        }


    }


}
