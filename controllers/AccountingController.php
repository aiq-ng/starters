<?php

namespace Controllers;

use Models\Accounting;
use Models\Kitchen;
use Services\NotificationService;

class AccountingController extends BaseController
{
    private $accounting;
    private $kitchen;

    public function __construct()
    {
        parent::__construct();
        $this->accounting = new Accounting();
        $this->kitchen = new Kitchen();
    }

    public function createExpense()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        $data['processed_by'] = $_SESSION['user_id'];

        error_log('Expense Data: ' . json_encode($data));

        try {
            $this->accounting->insertExpense($data);
        } catch (\Exception $e) {
            $this->sendResponse($e->getMessage(), 400);
        }

        $this->sendResponse('Expense added', 200);
    }

    public function getExpenses()
    {
        $this->authorizeRequest();

        $params = [
            'page' => isset($_GET['page']) ? (int)$_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? (int)$_GET['page_size'] : 10,
            'search' => isset($_GET['search']) ? $_GET['search'] : null,
        ];

        $expenses = $this->accounting->getExpenses($params);

        if ($expenses) {
            $this->sendResponse('success', 200, $expenses['data'], $expenses['meta']);
        } else {
            $this->sendResponse('Expenses not found', 404);
        }
    }

    public function getExpense($id)
    {
        $this->authorizeRequest();

        $expense = $this->accounting->getExpense($id);

        if ($expense) {
            $this->sendResponse('success', 200, $expense);
        } else {
            $this->sendResponse('Expense not found', 404);
        }
    }

    public function deleteExpense()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();
        $ids = isset($data['ids']) ? (array)$data['ids'] : [];

        $result = $this->accounting->deleteExpense($ids);

        if ($result) {
            $this->sendResponse('Expense deleted successfully', 200);
        } else {
            $this->sendResponse('Expense not found', 404);
        }
    }

    public function getBills()
    {
        $this->authorizeRequest();

        $params = [
            'search' => isset($_GET['search']) ? $_GET['search'] : null,
            'page' => isset($_GET['page']) ? (int)$_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? (int)$_GET['page_size'] : 10,
            'status' => isset($_GET['status']) ? $_GET['status'] : null,
            'start_date' => isset($_GET['start_date']) && !empty($_GET['start_date'])
            ? $_GET['start_date']
            : null,
            'end_date' => isset($_GET['end_date']) && !empty($_GET['end_date'])
            ? $_GET['end_date']
            : date('Y-m-d')

        ];

        $bills = $this->accounting->getBills($params);

        if ($bills) {
            $this->sendResponse('success', 200, $bills['data'], $bills['meta']);
        } else {
            $this->sendResponse('Bills not found', 404);
        }
    }

    public function getSalesOrder($orderId)
    {
        $this->authorizeRequest();

        $order = $this->accounting->getSalesOrder($orderId);

        if ($order) {
            $this->sendResponse('success', 200, $order);
        } else {
            $this->sendResponse('Order not found', 404);
        }
    }

    public function confirmSalesOrderPayment($orderId)
    {
        $this->authorizeRequest();

        try {
            $this->accounting->confirmSalesOrderPayment($orderId);
        } catch (\Exception $e) {
            $this->sendResponse($e->getMessage(), 400);
        }

        $this->sendResponse('Order payment confirmed', 200);
    }

    public function markBillAsPaid($billId)
    {
        $this->authorizeRequest();

        try {
            $this->accounting->markBillAsPaid($billId);
        } catch (\Exception $e) {
            $this->sendResponse($e->getMessage(), 400);
        }

        $this->sendResponse('Bill marked as paid', 200);
    }

    public function overview()
    {
        $this->authorizeRequest();

        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');

        $year = (int)$year;

        if ($year <= 0 || strlen((string)$year) != 4) {
            $year = (int)date('Y');
        }


        $overview = $this->accounting->getAccountingOverview($year);

        if ($overview) {
            $this->sendResponse('success', 200, $overview);
        } else {
            $this->sendResponse('Sales overview not found', 404);
        }
    }

    public function revenueAndExpensesGraph()
    {
        $this->authorizeRequest();

        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');

        $year = (int)$year;

        if ($year <= 0 || strlen((string)$year) != 4) {
            $year = (int)date('Y');
        }

        $data = $this->accounting->getRevenueAndExpensesByYear($year);

        if ($data) {
            $this->sendResponse('success', 200, $data['data'], $data['meta']);
        } else {
            $this->sendResponse('Data not found', 404);
        }
    }

}
