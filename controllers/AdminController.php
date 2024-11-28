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

    public function registerAdmin()
    {
        $data = $this->getRequestData();

        if (!$this->validateFields($data['username'], $data['password'])) {
            $this->sendResponse('Incomplete data provided', 400);
            return;
        }

        if ($this->admin->getAdmin($data['username'])) {
            $this->sendResponse('Username already exists', 400);
            return;
        }

        $result = $this->admin->registerAdmins($data);

        if ($result) {
            $this->sendResponse('success', 201, ['admins_id' => $result]);
        } else {
            $this->sendResponse('Error registering admin', 500);
        }
    }


    public function numberOfAdmins()
    {
        $number = $this->admin->getNumberOfAdmins();
        $this->sendResponse('success', 200, $number);

    }









}