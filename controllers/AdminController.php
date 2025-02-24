<?php

namespace Controllers;

use Models\Admin;

class AdminController extends BaseController
{
    private $admin;

    public function __construct()
    {
        parent::__construct();
        $this->admin = new Admin();
    }

    public function overview()
    {
        $this->authorizeRequest();

        $data = $this->admin->getPermissionByUserCount();

        if ($data) {
            $this->sendResponse('success', 200, $data);
        } else {
            $this->sendResponse('Overview not found', 404);
        }
    }

}
