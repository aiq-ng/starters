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

    public function createAdmin()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        $user = $this->getUserByEmail($data['email']);

        if (!$this->validateFields(
            $data['email'],
            $data['password'],
            $data['permissions']
        )) {
            $this->sendResponse('Invalid input data', 400);
        }

        $userId = $this->admin->addAdminAccess($user['id'], $data);

        if (!$userId) {
            $this->sendResponse('Error adding admin access', 500);
        }

        $templateVariables = [
            'name' => $user['name'],
            'email' => $user['email'],
            'password' => $data['password'],
            'login_link' => getenv('APP_URL') . '/login',
        ];

        $emailSent = $this->emailService->sendLoginDetails(
            $user['email'],
            $templateVariables['name'],
            $templateVariables
        );

        if (!$emailSent) {
            $this->sendResponse('Admin access added, but email sending failed', 500);
        }

        $this->sendResponse('Admin access added successfully', 201, ['user_id' => $userId]);
    }

}
