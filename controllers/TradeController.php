<?php

namespace Controllers;

use Models\Purchase;
use Models\Sale;
use Services\RedisService;
use Ramsey\Uuid\Uuid;

class TradeController extends BaseController
{
    private $purchase;
    private $sale;
    private $redis;

    public function __construct()
    {
        parent::__construct();
        $this->purchase = new Purchase();
        $this->sale = new Sale();
        $this->redis = new RedisService();
    }

    private function handleCombinedData(array $drafts, array $ordersData, array $filters): array
    {
        $combinedData = [];

        if (!empty($ordersData)) {
            $combinedData = array_merge($drafts, $ordersData);
        } elseif (!empty($drafts)) {
            $combinedData = $drafts;
        } else {
            error_log("No orders or drafts");
        }

        if (!empty($combinedData)) {
            usort($combinedData, function ($a, $b) {
                $aTime = isset($a['created_datetime']) ? strtotime($a['created_datetime']) : 0;
                $bTime = isset($b['created_datetime']) ? strtotime($b['created_datetime']) : 0;
                return $bTime - $aTime;
            });
        }

        if (!empty($combinedData) && !is_null($filters['status']) && $filters['status'] !== 'all') {
            $combinedData = array_filter($combinedData, function ($item) use ($filters) {
                return isset($item['status']) && $item['status'] === $filters['status'];
            });
        }

        $totalData = count($combinedData);
        $pageSize = (int) $filters['page_size'];
        $totalPages = ($pageSize > 0) ? max(1, ceil($totalData / $pageSize)) : 1;
        $currentPage = max(1, min((int) $filters['page'], $totalPages));
        $offset = ($currentPage - 1) * $pageSize;

        $paginatedData = array_slice($combinedData, $offset, $pageSize);

        $meta = [
            'total_data' => $totalData,
            'total_pages' => $totalPages,
            'page_size' => $pageSize,
            'previous_page' => ($currentPage > 1) ? $currentPage - 1 : '',
            'current_page' => $currentPage,
            'next_page' => ($currentPage < $totalPages) ? $currentPage + 1 : ''
        ];

        return [
            'data' => $paginatedData,
            'meta' => $meta
        ];
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
        $userId = $_SESSION['user_id'];
        $draftKey = "drafts:purchase_orders:{$userId}";
        $drafts = $this->redis->getList($draftKey);

        $result = $this->handleCombinedData($drafts, $purchases['data'] ?? [], $filters);

        if ($result['data']) {
            $this->sendResponse('success', 200, $result['data'], $result['meta']);
        } else {
            $this->sendResponse('Purchases not found', 200);
        }
    }

    public function saleIndex()
    {
        $this->authorizeRequest();

        $filters = [
            'search' => isset($_GET['search']) ? $_GET['search'] : null,
            'page' => isset($_GET['page']) ? $_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? $_GET['page_size'] : 10,
            'status' => isset($_GET['status']) ? $this->convertStatus($_GET['status']) : null,
            'order_type' => isset($_GET['order_type']) ? $_GET['order_type'] : null,
            'start_date' => !empty($_GET['start_date']) ? $_GET['start_date'] : null,
            'end_date' => !empty($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'),
        ];

        $sales = $this->sale->getSalesOrders(array_filter($filters));
        $userId = $_SESSION['user_id'];
        $draftKey = "drafts:sales_orders:{$userId}";

        $drafts = $this->redis->getList($draftKey);

        $result = $this->handleCombinedData($drafts, $sales['data'] ?? [], $filters);

        if ($result['data']) {
            $this->sendResponse('success', 200, $result['data'], $result['meta']);
        } else {
            $this->sendResponse('Sales not found', 200);
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

        if (!is_array($data['items']) || empty($data['items'])) {
            $this->sendResponse('Items should be an array and not empty', 400);
        }

        $data['user_id'] = $_SESSION['user_id'];
        $dataType = isset($data['type']) ? $data['type'] : null;

        if ($dataType && $dataType === 'draft') {
            $data['data_type'] = 'purchase_orders';
            $this->saveDraftOrder($data);
            return;
        }

        error_log('Data: ' . json_encode($data));
        $invoice = $this->purchase->createPurchase($data);

        if ($invoice && isset($data['id'])) {
            $draftKey = "drafts:purchase_orders:{$data['user_id']}";
            $this->redis->deleteByIds($draftKey, [$data['id']]);
        }

        if ($invoice) {
            $this->insertAuditLog(
                userId: $invoice['processed_by'],
                entityId: $invoice['id'],
                entityType: 'purchase_invoice',
                action: 'create',
                entityData: [
                    'reference_number' => $invoice['reference_number'] ?? null,
                    'invoice_number' => $invoice['invoice_number'] ?? null,
                    'order_id' => $invoice['purchase_order_number'] ?? null,
                    'recipient_id' => $invoice['vendor_id'] ?? null,
                    'recipient_name' => $invoice['vendor_name'] ?? null,
                    'total' => $invoice['total'] ?? null,
                    'status' => $invoice['status'] ?? null,
                    'message' => 'invoice created for ' .
                        '₦' . number_format($invoice['total'] ?? 0, 2)
                ]
            );

            $user = $this->findRecord('users', $data['user_id']);
            $usersToNotify = BaseController::getUserByRole('Admin');

            if (empty($usersToNotify)) {
                throw new \Exception("No Admin user found for notification.");
            }

            foreach ($usersToNotify as $userToNotify) {
                if (!isset($userToNotify['id'])) {
                    continue;
                }

                $notification = [
                    'user_id' => $userToNotify['id'],
                    'event' => 'notification',
                    'entity_id' => $invoice['id'],
                    'entity_type' => "purchase_order",
                    'title' => 'New Purchase Order',
                    'body' => $user['name'] . ' has created a new purchase order',
                ];

                $this->notify->sendNotification($notification);
            }

            $this->sendResponse('success', 201, $invoice);
        } else {
            $this->sendResponse('Failed to create purchase', 500);
        }
    }

    public function createSale()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        $data['user_id'] = $_SESSION['user_id'];

        $dataType = isset($data['type']) ? $data['type'] : null;

        if ($dataType && $dataType === 'draft') {
            $data['data_type'] = 'sales_orders';
            $this->saveDraftOrder($data);
            return;
        }


        error_log('Data: ' . json_encode($data));
        $sale = $this->sale->createSale($data);

        if (!$sale) {
            $this->sendResponse('Failed to create sale', 500);
        }

        if ($sale && isset($data['id'])) {
            $draftKey = "drafts:sales_orders:{$data['user_id']}";
            $this->redis->deleteByIds($draftKey, [$data['id']]);
        }


        $this->insertAuditLog(
            userId: $sale['processed_by'],
            entityId: $sale['id'],
            entityType: 'sales_invoice',
            action: 'create',
            entityData: [
                'reference_number' => $sale['reference_number'] ?? null,
                'invoice_number' => $sale['invoice_number'] ?? null,
                'order_id' => $sale['order_id'] ?? null,
                'recipient_id' => $sale['customer_id'] ?? null,
                'recipient_name' => $sale['customer_name'] ?? null,
                'total' => $sale['total'] ?? null,
                'status' => $sale['status'] ?? null,
                'message' => 'invoice created for ' .
                    '₦' . number_format($sale['total'] ?? 0, 2)
            ]
        );

        $user = $this->findRecord('users', $data['user_id']);
        $usersToNotify = BaseController::getUserByRole('Admin');

        if (empty($usersToNotify)) {
            throw new \Exception("No Admin user found for notification.");
        }

        foreach ($usersToNotify as $userToNotify) {
            if (!isset($userToNotify['id'])) {
                continue;
            }

            $notification = [
                'user_id' => $userToNotify['id'],
                'event' => 'notification',
                'entity_id' => $sale['id'],
                'entity_type' => "sales_order",
                'title' => 'New Sales Order',
                'body' => $user['name'] . ' has created a new sales order',
            ];

            $this->notify->sendNotification($notification);
        }


        $this->sendResponse('success', 201, ['sale_id' => $sale['id']]);
    }

    public function saveDraftOrder($data)
    {
        if (!in_array($data['data_type'], ['purchase_orders', 'sales_orders'])) {
            $this->sendResponse('Invalid type', 400);
            return;
        }

        $draftKey = "drafts:{$data['data_type']}:{$data['user_id']}";
        $data['id'] = Uuid::uuid4()->toString();
        $data['is_draft'] = true;
        $data['status'] = 'draft';
        $data['created_datetime'] = time();

        $result = $this->redis->set($draftKey, $data);

        if ($result) {
            $this->redis->expire($draftKey, 2592000); // 30 days
            $this->sendResponse('Draft order saved successfully', 200, [
                'draft_id' => $data['id'],
                'user_id' => $data['user_id'],
                'total_drafts' => $result
            ]);
        } else {
            $this->sendResponse('Failed to save draft order', 500);
        }
    }

    public function updatePurchase($purchaseId)
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        $data['processed_by'] = $_SESSION['user_id'];

        try {

            $previousInvoiceTotal = $this->getInvoiceTotal($purchaseId, 'purchase_orders');
            error_log('Data: ' . json_encode($data));
            $invoice = $this->purchase->updatePurchaseOrder($purchaseId, $data);

            if ($invoice) {
                $newTotal = $invoice['total'] ?? 0;

                $this->insertAuditLog(
                    userId: $invoice['processed_by'],
                    entityId: $invoice['id'],
                    entityType: 'purchase_invoice',
                    action: 'update',
                    entityData: [
                        'reference_number' => $invoice['reference_number'] ?? null,
                        'invoice_number' => $invoice['invoice_number'] ?? null,
                        'order_id' => $invoice['purchase_order_number'] ?? null,
                        'recipient_id' => $invoice['vendor_id'] ?? null,
                        'recipient_name' => $invoice['vendor_name'] ?? null,
                        'total' => $newTotal,
                        'status' => $invoice['status'] ?? null,
                        'message' => 'Invoice amount updated from ' .
                            '₦' . number_format($previousInvoiceTotal, 2) .
                            ' to ' . '₦' . number_format($newTotal, 2)
                    ]
                );
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

        if (empty($ids)) {
            $this->sendResponse('No Purchase Order IDs provided', 400);
            return;
        }

        $invoices = [];
        foreach ($ids as $id) {
            $invoice = $this->purchase->getInvoiceDetails($id);
            if ($invoice) {
                $invoices[$id] = $invoice;
            }
        }

        $deleted = $this->purchase->deletePurchaseOrder($ids);

        $userId = $_SESSION['user_id'];
        $draftKey = "drafts:purchase_orders:{$userId}";
        $redisDeleted = $this->redis->deleteByIds($draftKey, $ids);

        if (!$redisDeleted) {
            $this->sendResponse('Failed to delete drafts', 500);
        }

        foreach ($invoices as $id => $invoice) {
            $this->insertAuditLog(
                userId: $invoice['processed_by'],
                entityId: $invoice['id'],
                entityType: 'purchase_invoice',
                action: 'delete',
                entityData: [
                    'reference_number' => $invoice['reference_number'] ?? null,
                    'invoice_number' => $invoice['invoice_number'] ?? null,
                    'order_id' => $invoice['purchase_order_number'] ?? null,
                    'recipient_id' => $invoice['vendor_id'] ?? null,
                    'recipient_name' => $invoice['vendor_name'] ?? null,
                    'total' => $invoice['total'] ?? null,
                    'status' => $invoice['status'] ?? null,
                    'message' => 'Payment of ' .
                        '₦' . number_format($invoice['total'] ?? 0, 2) .
                        ' deleted'
                ]
            );
        }

        $status = $deleted ? 200 : 404;
        $message = $deleted
            ? 'Purchase Order deleted successfully'
            : 'Purchase Order not found';

        $this->sendResponse($message, $status);
    }


    public function updateSales($saleId)
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();


        $data['processed_by'] = $_SESSION['user_id'];

        try {
            $previousInvoiceTotal = $this->getInvoiceTotal($saleId, 'sales_orders');

            error_log('Data: ' . json_encode($data));
            $invoice = $this->sale->updateSale($saleId, $data);

            if ($invoice) {
                $newTotal = $invoice['total'] ?? 0;

                $this->insertAuditLog(
                    userId: $invoice['processed_by'],
                    entityId: $invoice['id'],
                    entityType: 'sales_invoice',
                    action: 'update',
                    entityData: [
                        'reference_number' => $invoice['reference_number'] ?? null,
                        'invoice_number' => $invoice['invoice_number'] ?? null,
                        'order_id' => $invoice['order_id'] ?? null,
                        'recipient_id' => $invoice['customer_id'] ?? null,
                        'recipient_name' => $invoice['customer_name'] ?? null,
                        'total' => $newTotal,
                        'status' => $invoice['status'] ?? null,
                        'message' => 'Invoice amount updated from ' .
                            '₦' . number_format($previousInvoiceTotal, 2) .
                            ' to ' . '₦' . number_format($newTotal, 2)
                    ]
                );

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

        if (empty($ids)) {
            $this->sendResponse('No Sales Order IDs provided', 400);
            return;
        }

        $invoices = [];
        foreach ($ids as $id) {
            $invoice = $this->sale->getInvoiceDetails($id);
            if ($invoice) {
                $invoices[$id] = $invoice;
            }
        }

        $deleted = $this->sale->deleteSalesOrder($ids);
        $userId = $_SESSION['user_id'];
        $draftKey = "drafts:sales_orders:{$userId}";
        $redisDeleted = $this->redis->deleteByIds($draftKey, $ids);

        if (!$redisDeleted) {
            $this->sendResponse('Failed to delete drafts', 500);
        }


        foreach ($invoices as $id => $invoice) {
            $this->insertAuditLog(
                userId: $invoice['processed_by'],
                entityId: $invoice['id'],
                entityType: 'sales_invoice',
                action: 'delete',
                entityData: [
                    'reference_number' => $invoice['reference_number'] ?? null,
                    'invoice_number' => $invoice['invoice_number'] ?? null,
                    'order_id' => $invoice['order_id'] ?? null,
                    'recipient_id' => $invoice['customer_id'] ?? null,
                    'recipient_name' => $invoice['customer_name'] ?? null,
                    'total' => $invoice['total'] ?? null,
                    'status' => $invoice['status'] ?? null,
                    'message' => 'Payment of ' .
                        '₦' . number_format($invoice['total'] ?? 0, 2) .
                        ' deleted'
                ]
            );
        }

        $status = $deleted || $redisDeleted ? 200 : 404;
        $message = $deleted || $redisDeleted
            ? 'Sales Order deleted successfully'
            : 'Sales Order not found';

        $this->sendResponse($message, $status);
    }

    public function markPurchaseAsReceived($purchaseId)
    {
        $this->authorizeRequest();

        $result = $this->purchase->markAsReceived($purchaseId);

        if ($result) {

            $purchase = $this->findRecord('purchase_orders', $purchaseId);
            $usersToNotify = BaseController::getUserByRole(['Admin', 'Accountant']);

            if (empty($usersToNotify)) {
                throw new \Exception("No Admin user found for notification.");
            }

            foreach ($usersToNotify as $userToNotify) {
                if (!isset($userToNotify['id'])) {
                    continue;
                }

                $notification = [
                    'user_id' => $userToNotify['id'],
                    'event' => 'notification',
                    'entity_id' => $purchaseId,
                    'entity_type' => "purchase_order",
                    'title' => 'Purchase Order Received',
                    'body' => 'Purchase order ' . $purchase['purchase_order_number'] . ' has been marked as received',
                ];

                $this->notify->sendNotification($notification);
            }

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

    public function duplicateSale($saleId)
    {
        $this->authorizeRequest();

        $additionalData = [
            'user_id' => $_SESSION['user_id']
        ];
        $sale = $this->sale->duplicateSale($saleId, $additionalData);

        if (!$sale) {
            $this->sendResponse('Failed to duplicate sale', 500);
        }

        $this->insertAuditLog(
            userId: $sale['processed_by'],
            entityId: $sale['id'],
            entityType: 'sales_invoice',
            action: 'create',
            entityData: [
                'reference_number' => $sale['reference_number'] ?? null,
                'invoice_number' => $sale['invoice_number'] ?? null,
                'order_id' => $sale['order_id'] ?? null,
                'recipient_id' => $sale['customer_id'] ?? null,
                'recipient_name' => $sale['customer_name'] ?? null,
                'total' => $sale['total'] ?? null,
                'status' => $sale['status'] ?? null,
                'message' => 'invoice create for ' .
                    '₦' . number_format($sale['total'] ?? 0, 2)
            ]
        );

        $user = $this->findRecord('users', $additionalData['user_id']);
        $usersToNotify = BaseController::getUserByRole('Admin');

        if (empty($usersToNotify)) {
            throw new \Exception("No Admin user found for notification.");
        }

        foreach ($usersToNotify as $userToNotify) {
            if (!isset($userToNotify['id'])) {
                continue;
            }

            $notification = [
                'user_id' => $userToNotify['id'],
                'event' => 'notification',
                'entity_id' => $sale['id'],
                'entity_type' => "sales_order",
                'title' => 'New Sales Order',
                'body' => $user['name'] . ' has created a new sales order',
            ];

            $this->notify->sendNotification($notification);
        }


        $this->sendResponse('success', 201, ['sale_id' => $sale['id']]);
    }

    public function sendToKitchen()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();
        $ids = isset($data['ids']) ? (array) $data['ids'] : [];

        try {
            $this->sale->sendToKitchen($ids);
        } catch (\Exception $e) {
            $this->sendResponse($e->getMessage(), 400);
        }

        $this->sendResponse('Order sent to kitchen', 200);
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
        error_log('Sending purchase invoice'. $purchaseId);
        $this->authorizeRequest();

        $data = $this->getRequestData();
        $invoice = $data['files']['invoice'] ?? [];

        $response = $this->sendInvoiceEmail($purchaseId, 'purchase_orders', $invoice);

        if (!$response) {
            return $this->sendResponse('Failed to send invoice', 500);
        }

        $invoice = $this->purchase->getInvoiceDetails($purchaseId);

        $this->insertAuditLog(
            userId: $invoice['processed_by'],
            entityId: $invoice['id'],
            entityType: 'purchase_invoice',
            action: 'sent',
            entityData: [
                'reference_number' => $invoice['reference_number'] ?? null,
                'invoice_number' => $invoice['invoice_number'] ?? null,
                'order_id' => $invoice['purchase_order_number'] ?? null,
                'recipient_id' => $invoice['vendor_id'] ?? null,
                'recipient_name' => $invoice['vendor_name'] ?? null,
                'total' => $invoice['total'] ?? null,
                'status' => $invoice['status'] ?? null,
                'message' => 'Invoice for ' .
                    '₦' . number_format($invoice['total'] ?? 0, 2) .
                    ' sent to ' . ($invoice['vendor_name'] ?? 'Vendor')
            ]
        );

        return $this->sendResponse('Invoice sent successfully', 200);
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
        error_log('Sending invoice for sale: ' . $salesId);
        $this->authorizeRequest();

        $data = $this->getRequestData();
        $invoice = $data['files']['invoice'] ?? [];

        $response = $this->sendInvoiceEmail($salesId, 'sales_orders', $invoice);

        if (!$response) {
            return $this->sendResponse('Failed to send invoice', 500);
        }

        $invoice = $this->sale->getInvoiceDetails($salesId);

        $this->insertAuditLog(
            userId: $invoice['processed_by'],
            entityId: $invoice['id'],
            entityType: 'sales_invoice',
            action: 'sent',
            entityData: [
                'reference_number' => $invoice['reference_number'] ?? null,
                'invoice_number' => $invoice['invoice_number'] ?? null,
                'order_id' => $invoice['order_id'] ?? null,
                'recipient_id' => $invoice['customer_id'] ?? null,
                'recipient_name' => $invoice['customer_name'] ?? null,
                'total' => $invoice['total'] ?? null,
                'status' => $invoice['status'] ?? null,
                'message' => 'Invoice for ' .
                    '₦' . number_format($invoice['total'] ?? 0, 2) .
                    ' sent to ' . ($invoice['customer_name'] ?? 'Customer')
            ]
        );

        return $this->sendResponse('Invoice sent successfully', 200);
    }


    public function createPriceList()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        error_log('Data: ' . json_encode($data));

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

    public function voidSale($salesId)
    {
        $this->authorizeRequest();

        $result = $this->sale->voidSalesOrder($salesId);

        if ($result) {
            $this->sendResponse('success', 200);
        } else {
            $this->sendResponse('Failed to void sales order', 500);
        }
    }

    public function comment($id)
    {

        $this->authorizeRequest();

        $data = $this->getRequestData();

        $data['user_id'] = $_SESSION['user_id'];
        $data['entity_type'] = 'audit_logs';

        $result = $this->commentOnItemHistory($id, $data);

        if ($result) {
            $this->sendResponse('Success', 200, ['comment_id' => $result]);
        } else {
            $this->sendResponse('Failed to add comment', 500);
        }
    }

}
