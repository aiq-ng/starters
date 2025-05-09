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
            'sort_by' => isset($_GET['sort_by']) ? $_GET['sort_by'] : null,
            'sort_order' => isset($_GET['sort_order']) ? $_GET['sort_order'] : 'desc',
            'status' => isset($_GET['status']) ? $_GET['status'] : null,
            'search' => isset($_GET['search']) ? $_GET['search'] : null,
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

        $data['social_media'] = isset($data['social_media']) ? json_encode($data['social_media']) : null;

        error_log('Vendor Data: ' . json_encode($data));
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

        error_log('Vendor ID: ' . $id);

        $transactions = $this->vendor->getVendorTransactions($id);

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

        $data['social_media'] = isset($data['social_media']) ? json_encode($data['social_media']) : null;

        error_log('Vendor Data: ' . json_encode($data));
        $result = $this->vendor->updateVendor($id, $data);

        if ($result) {
            $this->sendResponse('success', 200);
        } else {
            $this->sendResponse('Vendor not updated', 500);
        }
    }

    public function delete()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();
        $ids = isset($data['ids']) ? (array) $data['ids'] : [];

        $result = $this->vendor->deleteVendor($ids);

        if ($result) {
            $this->sendResponse('success', 200);
        } else {
            $this->sendResponse('Vendor not deleted', 500);
        }
    }

}
