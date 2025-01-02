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
        $this->authorizeRequest();

        $overview = $this->dashboard->getOverview();
        $this->sendResponse('success', 200, $overview);
    }

    public function businessOverview()
    {
        $this->authorizeRequest();

        $filters = [
            'month' => isset($_GET['month']) ? $_GET['month'] : date('m'),
            'year' => isset($_GET['year']) ? $_GET['year'] : date('Y'),
        ];

        $businessOverview = $this->dashboard->getBusinessOverview($filters);
        $this->sendResponse('success', 200, $businessOverview);
    }

    public function lowQuantityStock()
    {
        $this->authorizeRequest();
        $filters = [
            'page' => isset($_GET['page']) ? $_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? $_GET['page_size'] : 10,
        ];

        try {
            $lqc = $this->dashboard->getLowQuantityStock($filters);
            $this->sendResponse('success', 200, $lqc['data'], $lqc['meta']);
        } catch (\Exception) {
            $this->sendResponse('error', 500, ['message' => 'Failed to fetch low quantity stock']);
        }
    }

    public function mostPurchased()
    {
        $this->authorizeRequest();
        $filters = [
            'page' => isset($_GET['page']) ? $_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? $_GET['page_size'] : 10,
            'month' => isset($_GET['month']) ? $_GET['month'] : date('m'),
            'year' => isset($_GET['year']) ? $_GET['year'] : date('Y'),
        ];
        $items = $this->dashboard->getMostPurchasedProducts($filters);

        if ($items) {
            $this->sendResponse('success', 200, $items['data'], $items['meta']);
        } else {
            $this->sendResponse('Items not found', 404);
        }
    }

    public function topSelling()
    {
        $this->authorizeRequest();
        $filters = [
            'page' => isset($_GET['page']) ? $_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? $_GET['page_size'] : 10,
            'month' => isset($_GET['month']) ? $_GET['month'] : date('m'),
            'year' => isset($_GET['year']) ? $_GET['year'] : date('Y'),
        ];
        $items = $this->dashboard->getBestSellingProducts($filters);

        if ($items) {
            $this->sendResponse('success', 200, $items['data'], $items['meta']);
        } else {
            $this->sendResponse('Items not found', 404);
        }
    }

    public function cashFlow()
    {
        $this->authorizeRequest();
        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');

        $year = (int)$year;

        if ($year <= 0 || strlen((string)$year) != 4) {
            // Fallback to the current year if the validation fails
            $year = (int)date('Y');
        }

        $cashFlow = $this->dashboard->getCashFlowByYear($year);

        if ($cashFlow) {
            $this->sendResponse('success', 200, $cashFlow['data'], $cashFlow['meta']);
        } else {
            $this->sendResponse('Cash flow not found', 404);
        }
    }
}
