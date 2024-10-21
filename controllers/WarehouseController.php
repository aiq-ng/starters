<?php

namespace Controllers;

use Models\Warehouse;

class WarehouseController extends BaseController
{
    private $warehouse;

    public function __construct()
    {
        parent::__construct();
        $this->warehouse = new Warehouse();
    }

    public function index()
    {
        $this->authorizeRequest();

        $warehouses = $this->warehouse->getWarehouses();

        if ($warehouses) {
            $this->sendResponse('success', 200, $warehouses);
        } else {
            $this->sendResponse('Warehouses not found', 404);
        }
    }

    public function create()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();
        if (!$this->validateFields($data['name'], $data['address'])) {
            $this->sendResponse('Invalid input data', 400);
        }

        $result = $this->warehouse->create($data);

        if ($result) {
            $this->sendResponse('success', 201, ['warehouse_id' => $result]);
        } else {
            $this->sendResponse('Warehouse not created', 500);
        }
    }

    public function show($id)
    {
        $this->authorizeRequest();

        $warehouse = $this->warehouse->getWarehouse($id);

        if ($warehouse) {
            $this->sendResponse('success', 200, $warehouse);
        } else {
            $this->sendResponse('Warehouse not found', 404);
        }
    }

}
