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
        $this->authorizeRequest();

        $filters = [
            'search' => isset($_GET['search']) ? $_GET['search'] : null,
            'page' => isset($_GET['page']) ? $_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? $_GET['page_size'] : 10,
            'status' => isset($_GET['status']) ? $_GET['status'] : null,
            'start_date' => isset($_GET['start_date']) && !empty($_GET['start_date']) ?? null,
            'end_date' => isset($_GET['end_date']) && !empty($_GET['end_date'])
            ? $_GET['end_date']
            : date('Y-m-d')
        ];


        $purchases = $this->purchase->getPurchaseOrders($filters);

        if ($purchases) {
            $this->sendResponse('success', 200, $purchases['data'], $purchases['meta']);
        } else {
            $this->sendResponse('Purchases not found', 404);
        }
    }

    public function showPurchase($purchaseId)
    {
        $this->authorizeRequest();

        $purchase = $this->purchase->getPurchaseOrder($purchaseId);

        if ($purchase) {
            $this->sendResponse('success', 200, $purchase);
        } else {
            $this->sendResponse('Purchase not found', 404);
        }
    }

    public function createPurchase()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        if (!$this->validateFields($data['vendor_id'])) {
            $this->sendResponse('Invalid fields', 400);
        }

        if (!is_array($data['items']) || empty($data['items'])) {
            $this->sendResponse('Items should be an array and not empty', 400);
        }

        $data['user_id'] = $_SESSION['user_id'];

        $invoice = $this->purchase->createPurchase($data);

        if ($invoice) {
            $this->sendResponse('success', 201, $invoice);
        } else {
            $this->sendResponse('Failed to create purchase', 500);
        }
    }

    public function updatePurchase($purchaseId)
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        if (!$this->validateFields($data['vendor_id'])) {
            $this->sendResponse('Invalid fields', 400);
        }

        if (!is_array($data['items']) || empty($data['items'])) {
            $this->sendResponse('Items should be an array and not empty', 400);
        }

        $data['user_id'] = $_SESSION['user_id'];

        try {
            $invoice = $this->purchase->updatePurchaseOrder($purchaseId, $data);

            if ($invoice) {
                $this->sendResponse('success', 200, $invoice);
            } else {
                $this->sendResponse('Failed to update purchase', 500);
            }
        } catch (\Exception $e) {
            $this->sendResponse('Error: ' . $e->getMessage(), 500);
        }
    }


    public function deletePurchaseOrder()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();
        $ids = isset($data['ids']) ? (array) $data['ids'] : [];

        $deleted = $this->purchase->deletePurchaseOrder($ids);

        $status = $deleted ? 200 : 500;
        $message = $deleted
            ? 'Purchase Order deleted successfully'
            : 'Failed to delete Purchase Order';

        $this->sendResponse($message, $status);
    }


    public function updateSales($saleId)
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        if (!$this->validateFields($data['vendor_id'])) {
            $this->sendResponse('Invalid fields', 400);
        }

        if (!is_array($data['items']) || empty($data['items'])) {
            $this->sendResponse('Items should be an array and not empty', 400);
        }

        $data['user_id'] = $_SESSION['user_id'];

        try {
            $invoice = $this->sale->updateSale($saleId, $data);

            if ($invoice) {
                $this->sendResponse('success', 200, $invoice);
            } else {
                $this->sendResponse('Failed to update sale', 500);
            }
        } catch (\Exception $e) {
            $this->sendResponse('Error: ' . $e->getMessage(), 500);
        }
    }


    public function deleteSalesOrder()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();
        $ids = isset($data['ids']) ? (array) $data['ids'] : [];

        $deleted = $this->sale->deleteSalesOrder($ids);

        if ($deleted) {
            $this->sendResponse('Sales Order deleted successfully', 200);
        } else {
            $this->sendResponse('Failed to delete Sales Order', 500);
        }
    }

    public function markPurchaseAsReceived($purchaseId)
    {
        $this->authorizeRequest();

        $result = $this->purchase->markAsReceived($purchaseId);

        error_log($result);

        if ($result) {

            $userToNotify =  BaseController::getUserByRole('Accountant');
            if (is_string($result)) {
                $vendorName = $result;
            } else {
                $vendorName = 'a vendor';
            }

            $notification = [
                'user_id' => $userToNotify['id'],
                'event' => 'notification',
                'entity_id' => $purchaseId,
                'entity_type' => "purchase_order",
                'title' => 'New Purchase Order',
                'body' => 'Purchase order from ' . $vendorName . ' has been received',
            ];

            $this->notify->sendNotification($notification);

            $this->sendResponse('success', 200);
        } else {
            $this->sendResponse('Failed to mark purchase as received', 500);
        }
    }

    public function salesOverview()
    {
        $this->authorizeRequest();

        $filter = [
            'when' => isset($_GET['when']) && !empty($_GET['when']) ? $_GET['when'] : 'yesterday'
        ];

        $overview = $this->sale->getSalesOverview($filter);

        if ($overview) {
            $this->sendResponse('success', 200, $overview);
        } else {
            $this->sendResponse('Sales overview not found', 404);
        }
    }

    public function topSellingStocks()
    {
        $this->authorizeRequest();

        $filter = [
            'page' => isset($_GET['page']) ? $_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? $_GET['page_size'] : 10,
        ];

        $topSelling = $this->sale->getTopSellingStock($filter);

        if ($topSelling) {
            $this->sendResponse('success', 200, $topSelling['data'], $topSelling['meta']);
        } else {
            $this->sendResponse('Top selling stock not found', 404);
        }
    }

    public function saleIndex()
    {
        $this->authorizeRequest();

        $filters = [
            'search' => isset($_GET['search']) ? $_GET['search'] : null,
            'page' => isset($_GET['page']) ? $_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? $_GET['page_size'] : 10,
            'status' => isset($_GET['status']) ? $_GET['status'] : null,
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

    public function upcomingEvents()
    {
        $this->authorizeRequest();

        $filter = [
            'page' => $_GET['page'] ?? 1,
            'page_size' => $_GET['page_size'] ?? 10,
            'search' => $_GET['search'] ?? null,
        ];

        $result = $this->sale->getServiceOrders($filter);

        if (!empty($result['data'])) {
            $this->sendResponse('success', 200, $result['data'], $result['meta']);
        } else {
            $this->sendResponse('No service orders found', 404);
        }
    }


    public function createSale()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        if (!$this->validateFields($data['order_type'])) {
            $this->sendResponse('Invalid fields', 400);
        }

        $data['user_id'] = $_SESSION['user_id'];

        $saleId = $this->sale->createSale($data);

        if (!$saleId) {
            $this->sendResponse('Failed to create sale', 500);
        }
        $this->sendResponse('success', 201, ['sale_id' => $saleId]);
    }

    public function patchSale($saleId)
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        $updateResult = $this->sale->patchSalesOrder($saleId, $data);

        if ($updateResult) {
            $this->sendResponse('sale updated successfully', 200);
        } else {
            $this->sendResponse('Failed to update sale', 500);
        }
    }


    public function getPurchaseInvoice($purchaseId)
    {
        $this->authorizeRequest();

        $invoice = $this->purchase->getInvoiceDetails($purchaseId);

        if ($invoice) {
            $this->sendResponse('success', 200, $invoice);
        } else {
            $this->sendResponse('Invoice not found', 404);
        }
    }

    public function sendPurchaseInvoice($purchaseId)
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();
        $invoice = $data['files']['invoice'] ?? [];

        $this->sendInvoiceEmail($purchaseId, 'purchase_orders', $invoice);

    }

    public function getSalesInvoice($salesId)
    {
        $this->authorizeRequest();

        $invoice = $this->sale->getInvoiceDetails($salesId);

        if ($invoice) {
            $this->sendResponse('success', 200, $invoice);
        } else {
            $this->sendResponse('Invoice not found', 404);
        }
    }

    public function sendSaleInvoice($salesId)
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();
        $invoice = $data['files']['invoice'] ?? [];

        $this->sendInvoiceEmail($salesId, 'sales_orders', $invoice);
    }


    public function createPriceList()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        if (!isset($data['list']) || !is_array($data['list'])) {
            return $this->sendResponse('Invalid data format.', 400);
        }

        $result = $this->sale->createPriceList($data['list']);
        if ($result) {
            $this->sendResponse('success', 201);
        } else {
            $this->sendResponse('Failed to add price lists', 500);
        }
    }

    public function getPriceList()
    {
        $this->authorizeRequest();

        $filters = [
            'page' => isset($_GET['page']) ? $_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? $_GET['page_size'] : 10,
            'item_category' => isset($_GET['item_category']) ? $_GET['item_category'] : null,
            'min_price' => isset($_GET['min_price']) ? $_GET['min_price'] : null,
            'max_price' => isset($_GET['max_price']) ? $_GET['max_price'] : null,
            'search' => isset($_GET['search']) ? $_GET['search'] : null,
        ];

        $priceList = $this->sale->getPriceList($filters);

        if ($priceList) {
            $this->sendResponse('success', 200, $priceList['data'], $priceList['meta']);
        } else {
            $this->sendResponse('Price list not found', 404);
        }
    }

    public function getApriceList($priceListId)
    {
        $this->authorizeRequest();

        $priceList = $this->sale->getAPriceList($priceListId);

        if ($priceList) {
            $this->sendResponse('success', 200, $priceList);
        } else {
            $this->sendResponse('Price list not found', 404);
        }
    }

    public function updatePriceList($priceListId)
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();
        $data['id'] = $priceListId;

        error_log(json_encode($data));

        if (!isset($data['unit_price'])) {
            return $this->sendResponse('Missing unit price.', 400);
        }

        $result = $this->sale->updatePriceList($data);
        if ($result) {
            $this->sendResponse('success', 200);
        } else {
            $this->sendResponse('Failed to update price list', 500);
        }
    }

    public function deletePriceList()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();
        $ids = isset($data['ids']) ? (array) $data['ids'] : [];

        $result = $this->sale->deletePriceList($ids);

        if ($result) {
            $this->sendResponse('success', 200);
        } else {
            $this->sendResponse('Failed to delete price list', 500);
        }
    }

    public function salesGraph()
    {
        $this->authorizeRequest();

        $period = isset($_GET['period']) ? $_GET['period'] : 'week';

        $graph = $this->sale->getRevenue($period);

        if ($graph) {
            $this->sendResponse('success', 200, $graph['data'], $graph['meta']);
        } else {
            $this->sendResponse('Graph not found', 404);
        }
    }

}
