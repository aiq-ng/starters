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

        $result = $this->inventory->updateStockItem($id, $formData, $mediaLinks);

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

        $data['user_id'] = $data['user_id'] ?? $_SESSION['user_id'];
        $data['user_department_id'] = $data['user_department_id'] ?? $data['department_id'];

        $result = $this->inventory->adjustStock($id, $data);

        if ($result) {
            $this->sendResponse('Success', 200);
        } else {
            $this->sendResponse('Failed to adjust item', 500);
        }

    }


}
