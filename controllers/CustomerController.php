<?php

namespace Controllers;

use Models\Customer;

class CustomerController extends BaseController
{
    private $customer;

    public function __construct()
    {
        parent::__construct();
        $this->customer = new Customer();
    }

    public function index()
    {
        $this->authorizeRequest();

        $filters = [
            'page' => isset($_GET['page']) ? $_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? $_GET['page_size'] : 10,
            'sort_by' => isset($_GET['sort_by']) ? $_GET['sort_by'] : null,
            'sort_order' => isset($_GET['sort_order']) ? $_GET['sort_order'] : 'desc',
            'status' => isset($_GET['status']) ? $_GET['status'] : null,
            'search' => isset($_GET['search']) ? $_GET['search'] : null,
        ];


        $customers = $this->customer->getCustomers($filters);

        if ($customers) {
            $this->sendResponse('success', 200, $customers['data'], $customers['meta']);
        } else {
            $this->sendResponse('Customers not found', 404);
        }
    }

    public function create()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        if (!$this->validateFields(
            $data['customer_type'],
            $data['first_name'],
            $data['last_name'],
            $data['company_name'],
            $data['email'],
            $data['currency_id'],
            $data['payment_term_id'],
        )) {
            $this->sendResponse('Invalid input data', 400);
        }

        $data['social_media'] = isset($data['social_media']) ? json_encode($data['social_media']) : null;

        $result = $this->customer->create($data);

        if ($result) {
            $this->sendResponse('success', 201, ['customer_id' => $result]);
        } else {
            $this->sendResponse('Customer not created', 500);
        }
    }

    public function show($id)
    {
        $this->authorizeRequest();

        $transactions = $this->customer->getCustomerTransactions($id);

        if ($transactions) {
            $this->sendResponse('success', 200, $transactions);
        } else {
            $this->sendResponse('Vendor not found', 404);
        }
    }

    public function update($id)
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        if (!$this->validateFields(
            $data['customer_type'],
            $data['first_name'],
            $data['last_name'],
            $data['company_name'],
            $data['email'],
            $data['currency_id'],
            $data['payment_term_id'],
        )) {
            $this->sendResponse('Invalid input data', 400);
        }

        $data['social_media'] = isset($data['social_media']) ? json_encode($data['social_media']) : null;

        $result = $this->customer->updateCustomer($id, $data);

        if ($result) {
            $this->sendResponse('success', 200);
        } else {
            $this->sendResponse('Customer not updated', 500);
        }
    }

    public function delete($id)
    {
        $this->authorizeRequest();

        $result = $this->customer->deleteCustomer($id);

        if ($result) {
            $this->sendResponse('success', 200);
        } else {
            $this->sendResponse('Customer not deleted', 500);
        }
    }
}
