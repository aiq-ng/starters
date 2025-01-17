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
            'status' => isset($_GET['status']) ? $_GET['status'] : 'received',
            'order_type' => isset($_GET['order_type']) ? $_GET['order_type'] : null,
            'start_date' => !empty($_GET['start_date']) ? $_GET['start_date'] : null,
            'end_date' => !empty($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'),
        ];

        $sales = $this->sale->getSalesOrders(array_filter($filters));

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

    public function markAsPrepared()
    {
        $this->authorizeRequest();

        try {
            $data = $this->getRequestData();

            if (empty($data['ids'])) {
                $this->sendResponse('Sales IDs are required', 400);
            }

            $ids = is_array($data['ids']) ? $data['ids'] : [$data['ids']];

            $this->kitchen->markAsPrepared($ids);

            $this->sendResponse('Sales marked as prepared', 200);
        } catch (\Exception $e) {
            $this->sendResponse('An error occurred: ' . $e->getMessage(), 500);
        }
    }

}
