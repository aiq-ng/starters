<?php

namespace Controllers;

use Models\Inventory;

class InventoryController extends BaseController
{
    private $inventory;


    public function __construct()
    {
        parent::__construct();
        $this->inventory = new Inventory();
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
            'search' => isset($_GET['search']) ? $_GET['search'] : null,
        ];

        error_log(json_encode($params));

        $inventory = $this->inventory->getInventory($params);

        if (empty($inventory)) {
            $this->sendResponse('Inventory not found', 404, []);
        }
        $this->sendResponse('success', 200, $inventory['inventory'], $inventory['meta']);
    }

    public function graph($itemId)
    {
        $this->authorizeRequest();
        $params = [
            'year' => isset($_GET['year']) ? $_GET['year'] : date('Y'),
            'month' => isset($_GET['month']) ? $_GET['month'] : date('m'),
        ];

        $graphData = $this->inventory->getItemPricesByDay($itemId, $params);

        if (empty($graphData)) {
            $this->sendResponse('Graph data not found', 404, []);
        }
        $this->sendResponse('success', 200, $graphData);
    }

    public function createItem()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        $formData = $data['form_data'];
        $mediaFiles = $data['files']['media'] ?? [];

        $requiredFields = [
            'name', 'department_id', 'category_id', 'manufacturer_id',
            'date_received', 'expiry_date', 'quantity', 'unit_id'
        ];

        $dataToValidate = array_intersect_key($formData, array_flip($requiredFields));

        if (!$this->validateFields(...array_values($dataToValidate))) {
            $this->sendResponse('Invalid input data', 400);
        }

        // Handle media files using MediaHandler
        $mediaLinks = [];
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


    public function showItem($id)
    {
        $this->authorizeRequest();

        $item = $this->inventory->getItem($id);

        if (empty($item)) {
            $this->sendResponse('item not found', 404, []);
        }
        $this->sendResponse('success', 200, $item);
    }

    public function updateItem($id)
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        $formData = $data['form_data'];
        $mediaFiles = $data['files']['media'] ?? [];

        $requiredFields = [
            'name', 'department_id', 'category_id', 'manufacturer_id',
            'date_received', 'expiry_date', 'quantity', 'unit_id'
        ];

        $dataToValidate = array_intersect_key($formData, array_flip($requiredFields));

        if (!$this->validateFields(...array_values($dataToValidate))) {
            $this->sendResponse('Invalid input data', 400);
        }

        $mediaLinks = [];
        if (!empty($mediaFiles)) {
            $mediaLinks = $this->mediaHandler->handleMediaFiles($mediaFiles);

            if ($mediaLinks === false) {
                $this->sendResponse('Error uploading media files', 500);
            }

        }

        $formData['manager_id'] = $formData['user_id'] ?? $_SESSION['user_id'];
        $formData['source_id'] = $formData['collector_id'] ?? $formData['vendor_id'] ?? $_SESSION['user_id'];
        $formData['source_department_id'] = $formData['user_department_id'] ?? $formData['department_id'];

        $result = $this->inventory->updateItem($id, $formData, $mediaLinks);

        if ($result) {
            $this->sendResponse('Success', 200, ['item_id' => $id]);
        } else {
            $this->sendResponse('Failed to update item', 500);
        }
    }

    public function adjustStock($id)
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        $data['manager_id'] = $_SESSION['user_id'];
        $data['source_id'] = $data['collector_id'] ?? $data['vendor_id'];
        $data['source_department_id'] = $data['collector_department_id'] ?? $data['receiving_department_id'];

        $result = $this->inventory->adjustStock($id, $data);

        if ($result) {
            $this->sendResponse('Success', 200, ['adjusted_stock_ids' => $result]);
        } else {
            $this->sendResponse('Failed to adjust item', 500);
        }

    }

    public function inventoryHistory($id)
    {
        $this->authorizeRequest();

        $history = $this->inventory->getAdjustmentHistory($id);

        if (empty($history)) {
            $this->sendResponse('History not found', 200, []);
        }
        $this->sendResponse('success', 200, $history);
    }

    public function comment($id)
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        $data['user_id'] = $_SESSION['user_id'];

        $result = $this->inventory->commentOnItemHistory($id, $data);

        if ($result) {
            $this->sendResponse('Success', 200, ['comment_id' => $result]);
        } else {
            $this->sendResponse('Failed to add comment', 500);
        }
    }

    public function deleteItem()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();
        $ids = isset($data['ids']) ? (array) $data['ids'] : [];

        $result = $this->inventory->deleteItem($ids);

        if ($result) {
            $this->sendResponse('Success', 200);
        } else {
            $this->sendResponse('Failed to delete item', 500);
        }
    }
}
