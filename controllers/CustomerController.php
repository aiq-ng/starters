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

        $customers = $this->customer->getCustomers();

        if ($customers) {
            $this->sendResponse('success', 200, $customers);
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

        $customer = $this->customer->getCustomer($id);

        if ($customer) {
            $this->sendResponse('success', 200, $customer);
        } else {
            $this->sendResponse('Customer not found', 404);
        }
    }

}
