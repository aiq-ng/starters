<?php

namespace Controllers;

use Models\Vendor;

class VendorController extends BaseController
{
    private $vendor;

    public function __construct()
    {
        parent::__construct();
        $this->vendor = new Vendor();
    }

    public function index()
    {
        $this->authorizeRequest();

        $filters = [
            'page' => isset($_GET['page']) ? $_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? $_GET['page_size'] : 10,
            'sort_by' => isset($_GET['sort_by']) ? $_GET['sort_by'] : 'created_at',
            'sort_order' => isset($_GET['sort_order']) ? $_GET['sort_order'] : 'desc',
        ];


        $vendors = $this->vendor->getVendors($filters);

        if ($vendors) {
            $this->sendResponse('success', 200, $vendors['data'], $vendors['meta']);
        } else {
            $this->sendResponse('Vendors not found', 404);
        }
    }

    public function create()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        if (!$this->validateFields(
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['category_id'],
            $data['currency_id'],
            $data['payment_term_id'],
        )) {
            $this->sendResponse('Invalid input data', 400);
        }

        $data['social_media'] = isset($data['social_media']) ? json_encode($data['social_media']) : null;

        $result = $this->vendor->create($data);

        if ($result) {
            $this->sendResponse('success', 201, ['vendor_id' => $result]);
        } else {
            $this->sendResponse('Vendor not created', 500);
        }
    }

    public function show($id)
    {
        $this->authorizeRequest();

        $vendor = $this->vendor->getVendor($id);

        if ($vendor) {
            $this->sendResponse('success', 200, $vendor);
        } else {
            $this->sendResponse('Vendor not found', 404);
        }
    }

}
