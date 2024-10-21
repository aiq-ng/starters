<?php

namespace Controllers;

use Models\Purchase;
use Models\Sale;

class TradeController extends BaseController
{
    private $purchase;
    private $sale;

    public function __construct()
    {
        parent::__construct();
        $this->purchase = new Purchase();
        $this->sale = new Sale();
    }

    public function purchaseIndex()
    {
        // $this->authorizeRequest();

        $purchases = $this->purchase->getPurchases();

        if ($purchases) {
            $this->sendResponse('success', 200, $purchases['data'], $purchases['meta']);
        } else {
            $this->sendResponse('Purchases not found', 404);
        }
    }

    public function createPurchase()
    {
        // $this->authorizeRequest();

        $data = $this->getRequestData();

        if (!$this->validateFields($data['date'], $data['supplier'])) {
            $this->sendResponse('Invalid fields', 400);
        }

        if (!is_array($data['items']) || empty($data['items'])) {
            $this->sendResponse('Items should be an array and not empty', 400);
        }

        error_log('Creating purchase: ' . json_encode($data));

        $purchaseId = $this->purchase->createPurchase($data);

        if ($purchaseId) {
            $this->sendResponse('success', 201, ['purchase_id' => $purchaseId]);
        } else {
            $this->sendResponse('Failed to create purchase', 500);
        }
    }

    public function saleIndex()
    {
        // $this->authorizeRequest();

        $sales = $this->sale->getSales();

        if ($sales) {
            $this->sendResponse('success', 200, $sales);
        } else {
            $this->sendResponse('Sales not found', 404);
        }
    }
}
