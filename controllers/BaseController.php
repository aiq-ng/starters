<?php

namespace Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Database\Database;
use Exception;
use Services\MediaHandler;
use Services\EmailService;

class BaseController
{
    protected $db;
    protected $secret_key;
    protected $algorithm;
    protected $exp_time;
    protected $mediaHandler;
    protected $emailService;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->secret_key = getenv('SECRET_KEY');
        $this->algorithm = getenv('ALGORITHM');
        $this->exp_time = getenv('ACCESS_TOKEN_EXPIRE_MINUTES');
        $this->mediaHandler = new MediaHandler();
        $this->emailService = new EmailService();
    }

    protected function getRequestData()
    {
        // Check if the request is JSON
        if (strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            // Check for JSON decoding errors
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendResponse('Invalid JSON format', 400);
            }

            return $data;
        }

        // If not JSON, return $_POST and handle multiple file uploads
        $files = [];
        foreach ($_FILES as $key => $fileArray) {
            if (is_array($fileArray['name'])) {
                foreach ($fileArray['name'] as $index => $fileName) {
                    $files[$key][] = [
                        'name' => $fileName,
                        'type' => $fileArray['type'][$index],
                        'tmp_name' => $fileArray['tmp_name'][$index],
                        'error' => $fileArray['error'][$index],
                        'size' => $fileArray['size'][$index],
                    ];
                }
            } else {
                $files[$key] = $fileArray; // Single file upload
            }
        }

        return [
            'form_data' => $_POST,
            'files' => $files
        ];
    }

    public function authorizeRequest()
    {
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $this->sendResponse('Authorization header not found', 401);
            return;
        }

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        list($bearer, $token) = explode(' ', $authHeader, 2);

        if (strcasecmp($bearer, 'Bearer') !== 0) {
            $this->sendResponse('Invalid authorization format', 401);
            return;
        }

        try {
            $decoded = JWT::decode($token, new Key($this->secret_key, $this->algorithm));

            if (!isset($decoded->data->id)) {
                $this->sendResponse('Invalid token structure', 401);
                return;
            }

            $_SESSION['user_id'] = $decoded->data->id;

        } catch (Exception $e) {
            $this->sendResponse($e->getMessage(), 401);
            return;
        }
    }


    protected function validateFields(...$fields)
    {
        foreach ($fields as $field) {
            if (empty($field)) {
                return false;
            }
        }
        return true;
    }

    protected function sendResponse($message, $statusCode, $data = [], $meta = [])
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        $response = [
            'message' => $message,
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        echo json_encode($response);
        exit;
    }

    protected function fetchData(string $table, array $columns = ['*'])
    {
        $columnsList = implode(', ', $columns);
        $query = "SELECT $columnsList FROM $table";
        $stmt = $this->db->query($query);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($result)) {
            throw new \Exception("No records found in the table '$table'.");
        }

        return $result;
    }

    protected function findRecord(string $table, int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM $table WHERE id = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            throw new \Exception("Record with ID '$id' not found in the table '$table'.");
        }

        return $result;
    }

    protected function getUserByEmail(string $email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            throw new \Exception("User with email '$email' not found.");
        }

        return $result;
    }

    public function getRoles()
    {
        return $this->sendResponse('success', 200, $this->fetchData('roles'));

    }

    public function isAdmin()
    {
        $stmt = $this->db->prepare("SELECT role_id FROM users WHERE id = :id");
        $stmt->bindParam(':id', $_SESSION['user_id'], \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['role_id'] == 1;
    }

    public function getUnits()
    {
        return $this->sendResponse('success', 200, $this->fetchData('units', ['id', 'name', 'abbreviation']));
    }

    public function getVendors()
    {
        return $this->sendResponse('success', 200, $this->fetchData('vendors'));
    }

    public function getCurrencies()
    {
        return $this->sendResponse('success', 200, $this->fetchData('currencies', ['id', 'name', 'symbol']));
    }

    public function getPaymentMethods()
    {
        return $this->sendResponse('success', 200, $this->fetchData('payment_methods', ['id', 'name', 'description']));
    }

    public function getPaymentTerms()
    {
        return $this->sendResponse('success', 200, $this->fetchData('payment_terms', ['id', 'name', 'description']));
    }

    public function getTaxes()
    {
        return $this->sendResponse('success', 200, $this->fetchData('taxes', ['id', 'name', 'rate',  'description']));
    }

    public function getDepartments()
    {
        return $this->sendResponse('success', 200, $this->fetchData('departments', ['id', 'name', 'description']));
    }

    public function getBranches()
    {
        return $this->sendResponse('success', 200, $this->fetchData('branches', ['id', 'name']));
    }

    public function getItemCategories()
    {
        return $this->sendResponse('success', 200, $this->fetchData('item_categories', ['id', 'name', 'description']));
    }

    public function getItemManufacturers()
    {
        return $this->sendResponse('success', 200, $this->fetchData('item_manufacturers', ['id', 'name', 'website']));
    }

    public function getBasePayTypes()
    {
        return $this->sendResponse('success', 200, $this->fetchData('base_pay_types'));
    }

    public function getUsers()
    {
        return $this->sendResponse('success', 200, $this->fetchData('users', ['id', 'name', 'email', 'role_id']));
    }

    public function getNoOfWorkingDays()
    {
        return $this->sendResponse('success', 200, $this->fetchData('no_of_working_days'));
    }

    public function getPermissions()
    {
        return $this->sendResponse('success', 200, $this->fetchData('permissions'));
    }
}
