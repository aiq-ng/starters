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

        $data = $this->humanResource->getOverview();

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

    public function getAdmins()
    {
        $this->authorizeRequest();

        $admins = $this->humanResource->getAdmins();

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
        ];


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

    public function deleteEmployee($id)
    {
        $this->authorizeRequest();

        if (!$this->isAdmin()) {
            $this->sendResponse('Unauthorized', 403);
        }

        $result = $this->humanResource->deleteEmployee($id);

        if ($result) {
            $this->sendResponse('Employee deleted successfully', 200);
        } else {
            $this->sendResponse('Employee not found', 404);
        }
    }
}
