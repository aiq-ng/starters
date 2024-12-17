<?php

namespace Controllers;

use Models\Employee;

class EmployeeController extends BaseController
{
    private $employee;

    public function __construct()
    {
        parent::__construct();
        $this->employee = new Employee();
    }


    public function index()
    {
        $this->authorizeRequest();


        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $pageSize = isset($_GET['pageSize']) ? intval($_GET['pageSize']) : 10;

        $totalEmployees = $this->employee->countEmployees(); // Get the total number of employees
        $totalPages = ceil($totalEmployees / $pageSize); // Calculate total pages

        $employees = $this->employee->getAllEmployees($page, $pageSize);


        $response = [
            'status' => 'success',
            'current_page' => $page,
            'page_size' => $pageSize,
            'total_pages' => $totalPages,
            'total_employees' => $totalEmployees,
            'data' => $employees
            ];

        $this->sendResponse("Success", 200, $response);
    }


    public function create()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();

        $formData = $data['form_data'] ?? [];
        $mediaFiles = $data['files']['nin']['passport'] ?? [];

        $requiredFields = [
            'firstname', 'lastname', 'department', 'salaries',
            'bank_details', 'date_of_birth', 'leave', 'date_of_employment'
        ];

        error_log(json_encode($formData));

        $dataToValidate = array_intersect_key($formData, array_flip($requiredFields));

        // Validate required fields
        foreach ($requiredFields as $field) {
            if (empty($formData[$field])) {
                $this->sendResponse('Missing required field: ' . $field, 400);
                return;
            }
        }
        if (!$this->validateFields(...array_values($dataToValidate))) {
            $this->sendResponse('Invalid input data', 400);
        }

        try {
            if (!empty($mediaFiles)) {
                $mediaLinks = $this->mediaHandler->handleMediaFiles($mediaFiles);

                if ($mediaLinks === false) {
                    $this->sendResponse('Error uploading media files', 500);
                }

            }

            $result = $this->employee->registerEmployee($formData, $mediaLinks);

            if ($result) {
                $this->sendResponse('Employee added successfully', 201, ['item_id' => $result]);
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->sendResponse('Error creating employee: ' . $e->getMessage(), 500);
        }


    }

    public function show($id)
    {
        $this->authorizeRequest();
        $employee = $this->employee->getEmployeeById($id);

        if (empty($employee) || !$employee) {
            $this->sendResponse('Employee not found', 404, []);
        }
        $this->sendResponse('success', 200, $employee);
    }

    public function delete($id)
    {

        $this->authorizeRequest();
        $employee = $this->employee->deleteEmployee($id);
        if (!$employee) {
            $this->sendResponse('Error deleting this Employee', 400);

        } else {

            $this->sendResponse('Employee has been deleted', 201);
        }


    }












}
