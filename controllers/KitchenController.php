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
            'status' => isset($_GET['status']) ? $_GET['status'] : null,
            'order_type' => isset($_GET['order_type']) ? $_GET['order_type'] : null,
            'date' => !empty($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'),
        ];

        $sales = $this->kitchen->getNewOrders(array_filter($filters));

        if (!empty($sales['data'])) {
            $this->sendResponse('success', 200, $sales['data'], $sales['meta']);
        } else {
            $this->sendResponse('Sales not found', 404);
        }
    }

    public function show($salesId)
    {
        $this->authorizeRequest();

        $invoice = $this->sale->getInvoiceDetails($salesId);

        $returnData = [
            'id' => $invoice['id'],
            'order_title' => $invoice['order_title'],
            'invoice_number' => $invoice['invoice_number'],
            'customer_name' => $invoice['customer_name'],
            'items' => $invoice['items'],
        ];

        if ($invoice) {
            $this->sendResponse('success', 200, $returnData);
        } else {
            $this->sendResponse('Data not found', 404);
        }
    }

    public function updateStatus($salesId)
    {
        $this->authorizeRequest();

        try {

            $status = isset($_GET['status']) ? $_GET['status'] : null;

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

            $chefId = isset($_GET['chef_id']) ? $_GET['chef_id'] : null;

            if (empty($chefId)) {
                $this->sendResponse('Chef ID is required', 400);
            }

            $this->kitchen->assignOrder($salesId, $chefId);

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

}
