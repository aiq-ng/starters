<?php

namespace Controllers;

use Models\HumanResource;

class HumanResourceController extends BaseController
{
    private $humanResource;

    public function __construct()
    {
        parent::__construct();
        $this->humanResource = new HumanResource();
    }

    public function overview()
    {
        $this->authorizeRequest();

        $adminId = $this->getRoleIdByName('Admin');

        $data = $this->humanResource->getOverview($adminId);

        if ($data) {
            $this->sendResponse('success', 200, $data);
        } else {
            $this->sendResponse('Overview not found', 404);
        }
    }


    public function createDepartment()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        if (!$this->validateFields(
            $data['name'],
            $data['salary_type'],
        )) {
            $this->sendResponse('Invalid input data', 400);
        }

        $this->humanResource->createDepartment($data);

        $this->sendResponse('Department created successfully', 201);
    }

    public function createEmployee()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        $formData = $data['form_data'] ?? [];
        $mediaFiles = $data['files'] ?? [];

        if (!$this->validateFields(
            $formData['email'],
            $formData['firstname'],
            $formData['lastname'],
            $formData['department_id'],
            $formData['role_id'],
            $formData['no_of_working_days_id'],
        )) {
            $this->sendResponse('Invalid input data', 400);
        }

        $mediaLinks = [];
        $mediaTypes = ['nin', 'passport', 'avatar_url'];

        foreach ($mediaTypes as $mediaType) {
            if (!empty($mediaFiles[$mediaType])) {
                $mediaLink = $this->mediaHandler->handleMediaFiles([$mediaFiles[$mediaType]]);

                if ($mediaLink === false) {
                    error_log("Error uploading {$mediaType} file");
                }

                $mediaLinks[$mediaType] = $mediaLink;
            }
        }

        $result = $this->humanResource->addEmployee($formData, $mediaLinks);

        if (!$result) {
            $this->sendResponse('Error adding employee', 500);
        }

        if ($formData['username'] && $formData['password']) {
            $templateVariables = [
                'name' => $formData['firstname'] . ' ' . $formData['lastname'],
                'email' => $formData['username'],
                'password' => $formData['password'],
                'login_link' => getenv('APP_URL') . '/login',
            ];

            try {
                $emailSent = $this->emailService->sendLoginDetails(
                    $formData['email'],
                    $templateVariables['name'],
                    $templateVariables
                );

                if (!$emailSent) {
                    $this->sendResponse('Admin access added, but email sending failed', 500);
                }
            } catch (\Exception $e) {
                error_log("Error sending email: " . $e->getMessage());
                $this->sendResponse('Admin access added, but email sending failed', 500);
            }
        }

        $this->sendResponse('Employee created successfully', 201);
    }

    public function getAdmins()
    {
        $this->authorizeRequest();

        $adminId = $this->getRoleIdByName('Admin');
        $admins = $this->humanResource->getAdmins($adminId);

        if ($admins) {
            $this->sendResponse('success', 200, $admins);
        } else {
            $this->sendResponse('Admins not found', 404);
        }
    }

    public function getEmployees()
    {
        $this->authorizeRequest();

        $filters = [
            'page' => isset($_GET['page']) ? $_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? $_GET['page_size'] : 10,
            'department' => isset($_GET['department']) ? $_GET['department'] : null,
            'search' => isset($_GET['search']) ? $_GET['search'] : null,
        ];

        $adminId = $this->getRoleIdByName('Admin');
        if ($adminId) {
            $filters['role_id'] = $adminId;
        }


        $employees = $this->humanResource->getEmployees($filters);

        if ($employees) {
            $this->sendResponse('success', 200, $employees['data'], $employees['meta']);
        } else {
            $this->sendResponse('Employees not found', 404);
        }
    }

    public function showEmployee($id)
    {
        $this->authorizeRequest();

        $employee = $this->humanResource->getEmployee($id);

        if ($employee) {
            $this->sendResponse('success', 200, $employee);
        } else {
            $this->sendResponse('Employee not found', 404);
        }
    }

    public function deleteEmployee()
    {
        $this->authorizeRequest();

        if (!$this->isAdmin()) {
            $this->sendResponse('Unauthorized', 403);
        }

        $data = $this->getRequestData();
        $ids = isset($data['ids']) ? (array) $data['ids'] : [];

        $result = $this->humanResource->deleteEmployee($ids);

        if ($result) {
            $this->sendResponse('Employee deleted successfully', 200);
        } else {
            $this->sendResponse('Employee not found', 404);
        }
    }

    public function applyLeave()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();
        $id = $_SESSION['user_id'];

        if (!$this->validateFields(
            $data['leave_type'],
            $data['end_date'],
        )) {
            $this->sendResponse('Invalid input data', 400);
        }

        $result = $this->humanResource->addToLeave($id, $data);

        if ($result) {
            $this->sendResponse('Leave booked successfully', 201);
        } else {
            $this->sendResponse('Leave not booked', 500);
        }
    }

    public function approveLeave($id)
    {
        $this->authorizeRequest();

        $result = $this->humanResource->putOnLeave($id);

        if ($result) {
            $this->sendResponse('Employee put on leave successfully', 200);
        } else {
            $this->sendResponse('Employee not found', 404);
        }
    }

    public function suspendEmployee($id)
    {
        $this->authorizeRequest();

        $result = $this->humanResource->suspendEmployee($id);

        if ($result) {
            $this->sendResponse('Employee suspended successfully', 200);
        } else {
            $this->sendResponse('Employee not found', 404);
        }
    }
}
