<?php

namespace Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Database\Database;
use Exception;
use Services\MediaHandler;
use Services\EmailService;
use Services\NotificationService;
use Models\Purchase;
use Models\Sale;

class BaseController
{
    protected $db;
    protected $secret_key;
    protected $algorithm;
    protected $exp_time;
    protected $mediaHandler;
    protected $emailService;
    protected $notify;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->secret_key = getenv('SECRET_KEY');
        $this->algorithm = getenv('ALGORITHM');
        $this->exp_time = getenv('ACCESS_TOKEN_EXPIRE_MINUTES');
        $this->mediaHandler = new MediaHandler();
        $this->emailService = new EmailService();
        $this->notify = new NotificationService();
    }

    protected function getRequestData()
    {
        if (isset($_SERVER['CONTENT_TYPE']) &&
            strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {

            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendResponse('Invalid JSON format', 400);
            }

            // Filter out null or empty fields
            return array_filter($data, function ($value) {
                return $value !== null && $value !== '';
            });
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

        $formData = array_filter($_POST, function ($value) {
            return $value !== null && $value !== '';
        });

        return [
            'form_data' => $formData,
            'files' => $files
        ];
    }

    protected function storeRefreshToken($userId, $refreshToken)
    {
        $query = "
        INSERT INTO refresh_tokens (user_id, token)
        VALUES (?, ?)
    ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, $refreshToken]);
    }

    protected function deleteRefreshToken($userId)
    {
        $query = "DELETE FROM refresh_tokens WHERE user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
    }


    protected function validateRefreshToken($userId, $refreshToken)
    {
        $query = "
            SELECT token FROM refresh_tokens
            WHERE user_id = ? AND token = ?
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, $refreshToken]);

        return $stmt->fetchColumn() !== false;
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

            if (!isset($decoded->data->id) || !isset($decoded->claim)) {
                $this->sendResponse('Invalid token structure', 401);
                return;
            }

            if ($decoded->claim !== 'access') {
                $this->sendResponse('Unauthorized: Invalid token type', 401);
                return;
            }

            if ($this->isUserActive($decoded->data->id) !== 'active') {
                $this->sendResponse('Unauthorized: User is not active', 401);
                return;
            }

            $_SESSION['user_id'] = $decoded->data->id;
            $_SESSION['role_id'] = $decoded->data->role;

        } catch (Exception $e) {
            $this->sendResponse($e->getMessage(), 401);
            return;
        }
    }

    protected function isUserActive($userId)
    {
        $query = "SELECT status FROM users WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $userId, \PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $user['status'] ?? null;
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
        } else {
            $response['data'] = [];
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        echo json_encode($response);
        exit;
    }

    public function sendNotification()
    {
        $this->authorizeRequest();

        $UserRoleId = $_SESSION['role_id'];

        $roleId = $this->getRoleIdByName("Admin");

        if ($UserRoleId !== $roleId) {
            return $this->sendResponse('Unauthorized', 403);
        }

        $data = $this->getRequestData();

        $notificationData = [
            'user_id' => $data['user_id'],
            'event' => 'notification',
            'entity_id' => $data['user_id'],
            'entity_type' => "account",
            'title' => 'New Notification',
            'body' => $data['message'],
        ];

        if ($this->notify->sendNotification($notificationData)) {
            $this->sendResponse('Notification sent successfully', 200);
        } else {
            $this->sendResponse('Failed to send notification', 500);
        }
    }

    public function getNotifications()
    {
        $this->authorizeRequest();

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $pageSize = isset($_GET['page_size']) ? (int) $_GET['page_size'] : 10;
        $userId = isset($_GET['user_id']) && !empty($_GET['user_id']) ? $_GET['user_id'] : $_SESSION['user_id'];

        $offset = ($page - 1) * $pageSize;

        $query = "SELECT * FROM notifications WHERE user_id = :user_id LIMIT :page_size OFFSET :offset";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':page_size', $pageSize);
        $stmt->bindParam(':offset', $offset);

        $stmt->execute();

        $notifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($notifications)) {
            $this->sendResponse('No notifications found', 200);
        } else {
            $this->sendResponse('success', 200, $notifications);
        }
    }

    public function rateOrder($id)
    {
        $data = $this->getRequestData();

        $query = " 
            INSERT INTO order_ratings (order_id, name, rating, review)
            VALUES (:order_id, :name, :rating, :review)
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':order_id', $id);
        $stmt->bindValue(':name', $data["name"] ?? "Anonymous");
        $stmt->bindValue(':rating', $data["rating"] ?? 0);
        $stmt->bindValue(':review', $data["review"] ?? "");

        $status = $stmt->execute()
            ? ['Order rated successfully', 201]
            : ['Failed to rate order', 500];

        $this->sendResponse(...$status);
    }

    public function convertStatus($status)
    {
        if ($status === 'new_order') {
            return 'new order';
        }

        if ($status === 'in_progress') {
            return 'in progress';
        }

        if ($status === 'in_delivery') {
            return 'in delivery';
        }

        return $status;
    }

    public static function getUserByRole($roleName)
    {
        $db = Database::getInstance()->getConnection();

        $roleQuery = "SELECT id FROM roles WHERE name = :name";
        $stmtRole = $db->prepare($roleQuery);
        $stmtRole->bindParam(':name', $roleName);
        $stmtRole->execute();
        $roleId = $stmtRole->fetch(\PDO::FETCH_ASSOC)['id'];

        if (!$roleId) {
            return null;
        }

        $userQuery = "SELECT * FROM users WHERE role_id = :role_id LIMIT 1";
        $stmtUser = $db->prepare($userQuery);
        $stmtUser->bindParam(':role_id', $roleId);
        $stmtUser->execute();

        $user = $stmtUser->fetch(\PDO::FETCH_ASSOC);
        return $user;
    }


    protected function findRecord(string $table, string $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM $table WHERE id = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            throw new \Exception("Record with ID '$id' not found in the table '$table'.");
        }

        return $result;
    }

    protected function getUserByUsernameOrEmail(string $identifier)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :identifier OR username = :identifier");
        $stmt->bindParam(':identifier', $identifier, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            throw new \Exception("User with identifier '$identifier' not found.");
        }

        return $result;
    }

    public function getRoleIdByName($roleName)
    {
        $stmt = $this->db->prepare("SELECT id FROM roles WHERE name = :name");
        $stmt->bindParam(':name', $roleName, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC)['id'] ?? null;
    }

    public function isAdmin()
    {
        $adminRoleId = $this->getRoleIdByName('Admin');

        if (!$adminRoleId) {
            return false;
        }

        $stmtUser = $this->db->prepare("SELECT role_id FROM users WHERE id = :id");
        $stmtUser->bindParam(':id', $_SESSION['user_id'], \PDO::PARAM_STR);
        $stmtUser->execute();
        $result = $stmtUser->fetch(\PDO::FETCH_ASSOC);

        return $result && $result['role_id'] === $adminRoleId;
    }

    public function sendInvoiceEmail($id, $type, $attachment)
    {
        if ($type === 'sales_orders') {
            $salesInvoice = (new Sale())->getInvoiceDetails($id);
            $name = $salesInvoice['customer_name'];
            $email = $salesInvoice['customer_email'];

        } elseif ($type === 'purchase_orders') {
            $purchaseInvoice = (new Purchase())->getInvoiceDetails($id);
            $name = $purchaseInvoice['vendor_name'];
            $email = $purchaseInvoice['vendor_email'];

        } else {
            throw new \Exception("Invalid record type '$type'.");
        }

        $templateVariables = [
                'name' => $name,
                'email' => $email,
                'invoice_link' => getenv('APP_URL') . '/login',
            ];

        try {
            $this->emailService->sendInvoice(
                $email,
                $templateVariables['name'],
                $templateVariables,
                $attachment
            );

        } catch (\Exception $e) {
            error_log("Error sending email: " . $e->getMessage());
            $this->sendResponse('Failed to send Invoice', 500);
        }
        $this->sendResponse('Invoice sent successfully', 200);
    }

    protected function fetchData(
        string $table,
        array $searchColumns = [],
        array $columns = ['*']
    ) {
        $filters = [
            'search' => isset($_GET['search']) ? $_GET['search'] : null,
            'page' => isset($_GET['page']) && is_numeric($_GET['page']) && (int)$_GET['page'] > 0
                ? (int)$_GET['page']
                : 1,
            'page_size' => isset($_GET['page_size']) && is_numeric($_GET['page_size']) && (int)$_GET['page_size'] > 0
                ? (int)$_GET['page_size']
                : 10,
        ];

        $page = $filters['page'];
        $perPage = $filters['page_size'];

        $searchConditions = [];
        if (!empty($filters['search'])) {
            foreach ($searchColumns as $column) {
                $searchConditions[$column] = $filters['search'];
            }
        }

        $columnsList = implode(', ', $columns);
        $offset = ($page - 1) * $perPage;
        $searchQuery = '';

        if (!empty($searchConditions)) {
            $searchClauses = [];
            foreach ($searchConditions as $column => $value) {
                $searchClauses[] = "$column ILIKE :$column";
            }
            $searchQuery = 'WHERE ' . implode(' OR ', $searchClauses);
        }

        $query = "SELECT $columnsList FROM $table $searchQuery LIMIT :perPage OFFSET :offset";
        $countQuery = "SELECT COUNT(*) as total FROM $table $searchQuery";

        $stmt = $this->db->prepare($query);
        $countStmt = $this->db->prepare($countQuery);

        if (!empty($searchConditions)) {
            foreach ($searchConditions as $column => $value) {
                $stmt->bindValue(":$column", "%$value%");
                $countStmt->bindValue(":$column", "%$value%");
            }
        }
        $stmt->bindValue(':perPage', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $countStmt->execute();
        $totalItems = (int) $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

        if (empty($result)) {
            return $this->sendResponse('Not Found', 404, []);
        }

        $totalPages = ceil($totalItems / $perPage);

        $meta = [
            'total_data' => $totalItems,
            'total_pages' => $totalPages,
            'page_size' => $perPage,
            'previous_page' => $page > 1 ? $page - 1 : null,
            'current_page' => $page,
            'next_page' => $page + 1 <= $totalPages ? $page + 1 : null,
        ];

        return ['data' => $result, 'meta' => $meta];
    }

    protected function insertData(string $table, array $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->db->prepare($query);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        if ($stmt->execute()) {
            return $this->sendResponse('success', 201, 'Record successfully inserted');
        } else {
            throw new \Exception("Failed to insert record into '$table'.");
        }
    }

    public function getRoles()
    {
        $result = $this->fetchData('roles', ['name'], ['id', 'name']);
        return $this->sendResponse('success', 200, $result['data'], $result['meta']);
    }

    public function createRole()
    {
        $data = [
            'name' => $_POST['name'],
        ];

        return $this->insertData('roles', $data);
    }

    public function getUnits()
    {

        $result = $this->fetchData(
            'units',
            ['name', 'abbreviation'],
            ['id', 'name', 'abbreviation']
        );

        return $this->sendResponse(
            'success',
            200,
            $result['data'],
            $result['meta']
        );

    }

    public function createUnit()
    {
        $data = [
            'name' => $_POST['name'],
            'abbreviation' => $_POST['abbreviation'],
        ];

        return $this->insertData('units', $data);
    }

    public function getVendorCategories()
    {
        $result = $this->fetchData(
            'vendor_categories',
            ['name', 'description'],
            ['id', 'name', 'description']
        );

        return $this->sendResponse(
            'success',
            200,
            $result['data'],
            $result['meta']
        );
    }

    public function createVendorCategory()
    {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
        ];

        return $this->insertData('vendor_categories', $data);
    }

    public function getCurrencies()
    {
        $result = $this->fetchData(
            'currencies',
            ['name', 'symbol', 'code'],
            ['id', 'name', 'symbol', 'code']
        );

        return $this->sendResponse(
            'success',
            200,
            $result['data'],
            $result['meta']
        );
    }

    public function createCurrency()
    {
        $data = [
            'name' => $_POST['name'],
            'symbol' => $_POST['symbol'],
            'code' => $_POST['code'],
        ];

        return $this->insertData('currencies', $data);
    }

    public function getPaymentMethods()
    {
        $result = $this->fetchData(
            'payment_methods',
            ['name', 'description'],
            ['id', 'name', 'description']
        );

        return $this->sendResponse(
            'success',
            200,
            $result['data'],
            $result['meta']
        );
    }

    public function createPaymentMethod()
    {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
        ];

        return $this->insertData('payment_methods', $data);
    }

    public function getPaymentTerms()
    {
        $result = $this->fetchData(
            'payment_terms',
            ['name', 'description'],
            ['id', 'name', 'description']
        );

        return $this->sendResponse(
            'success',
            200,
            $result['data'],
            $result['meta']
        );
    }

    public function createPaymentTerm()
    {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
        ];

        return $this->insertData('payment_terms', $data);
    }

    public function getTaxes()
    {
        $result = $this->fetchData(
            'taxes',
            ['name', 'rate', 'description'],
            ['id', 'name', 'rate', 'description']
        );

        return $this->sendResponse(
            'success',
            200,
            $result['data'],
            $result['meta']
        );
    }

    public function createTax()
    {
        $data = [
            'name' => $_POST['name'],
            'rate' => $_POST['rate'],
            'description' => $_POST['description'],
        ];

        return $this->insertData('taxes', $data);
    }

    public function getDepartments()
    {
        $result = $this->fetchData(
            'departments',
            ['name', 'description'],
            ['id', 'name', 'description']
        );

        return $this->sendResponse(
            'success',
            200,
            $result['data'],
            $result['meta']
        );
    }

    public function createDepartment()
    {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
        ];

        return $this->insertData('departments', $data);
    }

    public function getBranches()
    {
        $result = $this->fetchData(
            'branches',
            ['name', 'description'],
            ['id', 'name', 'description']
        );

        return $this->sendResponse(
            'success',
            200,
            $result['data'],
            $result['meta']
        );
    }

    public function createBranch()
    {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
        ];

        return $this->insertData('branches', $data);
    }

    public function getItemCategories()
    {
        $result = $this->fetchData(
            'item_categories',
            ['name', 'description'],
            ['id', 'name', 'description']
        );

        return $this->sendResponse(
            'success',
            200,
            $result['data'],
            $result['meta']
        );
    }

    public function createItemCategory()
    {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
        ];

        return $this->insertData('item_categories', $data);
    }

    public function getItemManufacturers()
    {
        $result = $this->fetchData(
            'item_manufacturers',
            ['name', 'website'],
            ['id', 'name', 'website']
        );

        return $this->sendResponse(
            'success',
            200,
            $result['data'],
            $result['meta']
        );
    }

    public function createItemManufacturer()
    {
        $data = [
            'name' => $_POST['name'],
            'website' => $_POST['website'],
        ];

        return $this->insertData('item_manufacturers', $data);
    }

    public function getBasePayTypes()
    {
        $result = $this->fetchData(
            'base_pay_types',
            ['name', 'description'],
            ['id', 'name', 'description']
        );

        return $this->sendResponse(
            'success',
            200,
            $result['data'],
            $result['meta']
        );
    }

    public function createBasePayType()
    {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
        ];

        return $this->insertData('base_pay_types', $data);
    }

    public function getUsers()
    {
        $result = $this->fetchData(
            'users',
            ['name', 'email'],
            ['id', 'name', 'email', 'role_id']
        );

        return $this->sendResponse(
            'success',
            200,
            $result['data'],
            $result['meta']
        );
    }

    public function getNoOfWorkingDays()
    {
        $result = $this->fetchData(
            'no_of_working_days',
            ['name', 'description'],
            ['id', 'name', 'description']
        );

        return $this->sendResponse(
            'success',
            200,
            $result['data'],
            $result['meta']
        );
    }

    public function createNoOfWorkingDays()
    {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
        ];

        return $this->insertData('no_of_working_days', $data);
    }

    public function getPermissions()
    {
        $result = $this->fetchData(
            'permissions',
            ['name', 'description'],
            ['id', 'name', 'description']
        );

        return $this->sendResponse(
            'success',
            200,
            $result['data'],
            $result['meta']
        );
    }

    public function createPermission()
    {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
        ];

        return $this->insertData('permissions', $data);
    }

    public function getExpensesCategories()
    {
        $result = $this->fetchData(
            'expenses_categories',
            ['name', 'description'],
            ['id', 'name', 'description']
        );

        return $this->sendResponse(
            'success',
            200,
            $result['data'],
            $result['meta']
        );
    }

    public function createExpensesCategory()
    {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
        ];

        return $this->insertData('expenses_categories', $data);
    }

    public function createWorkLeaveQualification()
    {
        $data = [
            'name' => $_POST['name'],
        ];

        return $this->insertData('work_leave_qualifications', $data);
    }

    public function getWorkLeaveQualifications()
    {
        $result = $this->fetchData(
            'work_leave_qualifications',
            ['name'],
            ['id', 'name']
        );

        return $this->sendResponse(
            'success',
            200,
            $result['data'],
            $result['meta']
        );
    }
}
