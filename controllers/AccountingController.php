<?php

namespace Controllers;

use Models\Accounting;

class AccountingController extends BaseController
{
    private $accounting;

    public function __construct()
    {
        parent::__construct();
        $this->accounting = new Accounting();
    }

    public function createExpense()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        if (!$this->validateFields(
            $data['expense_title'],
            $data['expense_category'],
            $data['payment_method_id'],
            $data['department_id'],
            $data['amount'],
            $data['date_of_expense'],
        )) {
            $this->sendResponse('Invalid input data', 400);
        }

        $data['processed_by'] = $_SESSION['user_id'];

        $this->accounting->insertExpense($data);

        $this->sendResponse('Expense added', 200);
    }

    public function getExpenses()
    {
        $this->authorizeRequest();

        $params = [
            'page' => isset($_GET['page']) ? (int)$_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? (int)$_GET['page_size'] : 10,
        ];

        $expenses = $this->accounting->getExpenses($params);

        if ($expenses) {
            $this->sendResponse('success', 200, $expenses['data'], $expenses['meta']);
        } else {
            $this->sendResponse('Expenses not found', 404);
        }
    }

    public function getBills()
    {
        $this->authorizeRequest();

        $params = [
            'page' => isset($_GET['page']) ? (int)$_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? (int)$_GET['page_size'] : 10,
            'status' => isset($_GET['status']) ? $_GET['status'] : null,
            'start_date' => isset($_GET['start_date']) && !empty($_GET['start_date'])
            ? $_GET['start_date']
            : date('Y-m-d'),
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
}
