<?php

namespace Controllers;

use Models\Dashboard;

class DashboardController extends BaseController
{
    private $dashboard;

    public function __construct()
    {
        parent::__construct();
        $this->dashboard = new Dashboard();
    }

    public function overview()
    {
        $overview = $this->dashboard->getOverview();
        $this->sendResponse('success', 200, $overview);
    }

    public function lowQuantityStock()
    {
        try {
            $lqc = $this->dashboard->getLowQuantityStock();
            $this->sendResponse('success', 200, $lqc);
        } catch (\Exception $e) {
            $this->sendResponse('error', 500, ['message' => 'Failed to fetch low quantity stock']);
        }
    }

    public function mostPurchased()
    {
        $filters = [
            'page' => isset($_GET['page']) ? $_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? $_GET['page_size'] : 10,
            'month' => isset($_GET['month']) ? $_GET['month'] : date('m'),
            'year' => isset($_GET['year']) ? $_GET['year'] : date('Y'),
        ];
        $items = $this->dashboard->getMostPurchasedItems($filters);

        if ($items) {
            $this->sendResponse('success', 200, $items['data'], $items['meta']);
        } else {
            $this->sendResponse('Items not found', 404);
        }
    }
}
