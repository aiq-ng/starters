<?php

namespace Controllers;

use Models\Kitchen;
use Models\Sale;

class KitchenController extends BaseController
{
    private $kitchen;
    private $sale;

    public function __construct()
    {
        parent::__construct();
        $this->kitchen = new Kitchen();
        $this->sale = new Sale();
    }

    public function index()
    {
        $this->authorizeRequest();

        $filters = [
            'search' => isset($_GET['search']) ? $_GET['search'] : null,
            'page' => isset($_GET['page']) ? $_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? $_GET['page_size'] : 10,
            'status' => isset($_GET['status']) ? $this->convertStatus($_GET['status']) : null,
            'order_type' => isset($_GET['order_type']) ? $_GET['order_type'] : null,
            'delivery_option' => isset($_GET['delivery_option']) ? $_GET['delivery_option'] : null,
            'date' => !empty($_GET['date']) ? $_GET['date'] : null,
            'time' => !empty($_GET['time']) ? $_GET['time'] : null,
        ];

        $sales = $this->kitchen->getNewOrders(array_filter($filters));

        if (!empty($sales['data'])) {
            $this->sendResponse('success', 200, $sales['data'], $sales['meta']);
        } else {
            $this->sendResponse('Sales not found', 200);
        }
    }

    public function show($salesId)
    {
        $this->authorizeRequest();

        $invoice = $this->sale->getOrderById($salesId);

        if ($invoice) {
            $this->sendResponse('success', 200, $invoice);
        } else {
            $this->sendResponse('Data not found', 404);
        }
    }

    public function updateStatus($salesId)
    {
        $this->authorizeRequest();

        try {

            $status = isset($_GET['status']) ? $this->convertStatus($_GET['status']) : null;

            if (empty($status)) {
                $this->sendResponse('Status is required', 400);
            }

            $this->kitchen->updateOrderStatus($salesId, $status);

            $this->sendResponse('Status updated', 200);
        } catch (\Exception $e) {
            $this->sendResponse('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    public function assignOrder($salesId)
    {
        $this->authorizeRequest();

        try {

            $driverId = isset($_GET['driver_id']) ? $_GET['driver_id'] : null;

            if (empty($driverId)) {
                $this->sendResponse('Driver ID is required', 400);
            }

            $this->kitchen->assignOrder($salesId, $driverId);

            $this->sendResponse('Order assigned', 200);

        } catch (\Exception $e) {
            $this->sendResponse('An error occurred: ' . $e->getMessage(), 500);
        }

    }

    public function getChefs()
    {
        $this->authorizeRequest();

        $chefId = $this->getRoleIdByName('Chef');

        $chefs = $this->kitchen->getChefs($chefId);

        if (!empty($chefs)) {
            $this->sendResponse('success', 200, $chefs);
        } else {
            $this->sendResponse('Chefs not found', 404);
        }
    }

    public function getRiders()
    {
        $this->authorizeRequest();

        $riderId = $this->getDepartmentIdByName('Dispatch Riders');

        $riders = $this->kitchen->getRiders($riderId);

        if (!empty($riders)) {
            $this->sendResponse('success', 200, $riders);
        } else {
            $this->sendResponse('Riders not found', 404);
        }
    }

    public function getChefOrders()
    {
        $this->authorizeRequest();

        $chefId = isset($_GET['chef_id']) ? $_GET['chef_id'] : null;
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $page_size = isset($_GET['page_size']) ? $_GET['page_size'] : 100;

        $id = $chefId ? $chefId : $_SESSION['user_id'];

        $orders = $this->kitchen->getOrders($id, null, $page, $page_size);

        if (!empty($orders)) {
            $this->sendResponse('success', 200, $orders['data'], $orders['meta']);
        } else {
            $this->sendResponse('Orders not found', 404);
        }
    }

    public function getAllChefOrders()
    {
        $this->authorizeRequest();

        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $page_size = isset($_GET['page_size']) ? $_GET['page_size'] : 100;
        $chefId = isset($_GET['chef_id']) ? $_GET['chef_id'] : null;
        $status = isset($_GET['status'])
            ? $this->convertStatus($_GET['status'])
            : ['new order', 'in progress'];


        $orders = $this->kitchen->getOrders($chefId, $status, $page, $page_size);

        if (!empty($orders)) {
            $this->sendResponse('success', 200, $orders['data'], $orders['meta']);
        } else {
            $this->sendResponse('Orders not found', 404);
        }
    }


}
