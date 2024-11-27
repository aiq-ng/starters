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
}
