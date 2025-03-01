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
use Models\Search;

class BaseController
{
    protected $db;
    protected $secret_key;
    protected $algorithm;
    protected $exp_time;
    protected $mediaHandler;
    protected $emailService;
    protected $notify;
    protected $search;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->secret_key = getenv('SECRET_KEY');
        $this->algorithm = getenv('ALGORITHM');
        $this->exp_time = getenv('ACCESS_TOKEN_EXPIRE_MINUTES');
        $this->mediaHandler = new MediaHandler();
        $this->emailService = new EmailService();
        $this->notify = new NotificationService();
        $this->search = new Search();
    }

    protected function getRequestData()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendResponse('Invalid JSON format', 400);
            }

            return array_filter($data, function ($value) {
                return $value !== null && $value !== '';
            });
        }

        if ($method === 'PUT' && strpos($contentType, 'multipart/form-data') !== false) {
            return $this->parseMultipartFormData();
        }

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
                $files[$key] = $fileArray;
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

    protected function parseMultipartFormData()
    {
        $inputStream = fopen("php://input", "r");
        $rawData = stream_get_contents($inputStream);
        fclose($inputStream);

        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        $boundary = $matches[1] ?? null;

        if (!$boundary) {
            error_log("No boundary found in Content-Type header.");
            return ['form_data' => [], 'files' => []];
        }

        $blocks = explode("--" . $boundary, $rawData);
        $formData = [];
        $files = [];

        foreach ($blocks as $block) {
            if (empty(trim($block))) {
                continue;
            }

            if (strpos($block, 'Content-Disposition: form-data;') !== false) {
                preg_match('/name="([^"]+)"/', $block, $nameMatch);
                $name = $nameMatch[1] ?? '';

                if (strpos($block, 'filename=') !== false) {
                    preg_match('/filename="([^"]+)"/', $block, $filenameMatch);
                    $filename = $filenameMatch[1] ?? '';

                    preg_match('/Content-Type: (.+)/', $block, $typeMatch);
                    $fileType = trim($typeMatch[1] ?? '');

                    $fileContentStart = strpos($block, "\r\n\r\n") + 4;
                    $fileContent = substr($block, $fileContentStart, strrpos($block, "\r\n") - $fileContentStart);

                    $tmpFile = tempnam(sys_get_temp_dir(), 'php');
                    file_put_contents($tmpFile, $fileContent);

                    // Wrap file data in an array to handle single and multiple files uniformly
                    if (!isset($files[$name])) {
                        $files[$name] = [];
                    }

                    $files[$name][] = [
                        'name' => $filename,
                        'type' => $fileType,
                        'tmp_name' => $tmpFile,
                        'error' => 0,
                        'size' => strlen($fileContent),
                    ];
                } else {
                    $value = trim(substr($block, strpos($block, "\r\n\r\n") + 4, -2));
                    $formData[$name] = $value;
                }
            }
        }

        return ['form_data' => $formData, 'files' => $files];
    }

    protected function storeRefreshToken($userId, $refreshToken)
    {
        try {
            $query = "
                INSERT INTO refresh_tokens (user_id, token)
                VALUES (?, ?)
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$userId, $refreshToken]);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Failed to store refresh token");
        }
    }

    protected function deleteRefreshToken($userId)
    {
        try {
            $query = "DELETE FROM refresh_tokens WHERE user_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$userId]);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Failed to delete refresh token");
        }
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

    public function insertAuditLog($userId, $action, $entityType, $entityId, $entityData = [])
    {
        try {
            $encodedEntityData = json_encode($entityData);

            $query = "
                INSERT INTO audit_logs (user_id, action, entity_type, entity_id, entity_data)
                VALUES (:user_id, :action, :entity_type, :entity_id, :entity_data)
            ";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':entity_type', $entityType);
            $stmt->bindParam(':entity_id', $entityId);
            $stmt->bindParam(':entity_data', $encodedEntityData);

            $stmt->execute();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Failed to insert audit log");
        }
    }

    public function getAuditLogs()
    {
        $this->authorizeRequest();

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $pageSize = isset($_GET['page_size']) ? (int) $_GET['page_size'] : 10;
        $filter = isset($_GET['filter']) ? $_GET['filter'] : null;

        $offset = ($page - 1) * $pageSize;

        $query = "
            SELECT audit_logs.*, users.name as processed_by
            FROM audit_logs
            LEFT JOIN users ON audit_logs.user_id = users.id
        ";

        if ($filter) {
            $query .= " WHERE audit_logs.entity_type = :filter";
        }

        $query .= "
            ORDER BY created_at DESC
            LIMIT :page_size OFFSET :offset
        ";

        $stmt = $this->db->prepare($query);

        if ($filter) {
            $stmt->bindParam(':filter', $filter);
        }
        $stmt->bindParam(':page_size', $pageSize);
        $stmt->bindParam(':offset', $offset);

        $stmt->execute();

        $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($logs as &$log) {
            if (!empty($log['entity_data'])) {
                $log['entity_data'] = json_decode($log['entity_data'], true);
            }
        }

        $countQuery = "
            SELECT COUNT(*) as total
            FROM audit_logs
            LEFT JOIN users ON audit_logs.user_id = users.id
        ";

        if ($filter) {
            $countQuery .= " WHERE audit_logs.entity_type = :filter";
        }

        $countStmt = $this->db->prepare($countQuery);
        if ($filter) {
            $countStmt->bindParam(':filter', $filter);
        }
        $countStmt->execute();
        $totalItems = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

        $meta = [
            'total_data' => (int) $totalItems,
            'total_pages' => ceil($totalItems / $pageSize),
            'page_size' => (int) $pageSize,
            'previous_page' => $page > 1 ? (int) $page - 1 : null,
            'current_page' => (int) $page,
            'next_page' => (int) $page + 1,
        ];

        $response = [
            'data' => $logs,
            'meta' => $meta
        ];

        if (empty($logs)) {
            $this->sendResponse('No audit logs found', 200);
        } else {
            $this->sendResponse('success', 200, $response['data'], $response['meta']);
        }
    }

    public function commentOnItemHistory($itemStockId, $data)
    {
        try {
            $sql = "
                INSERT INTO comments
                (entity_id, entity_type, user_id, parent_id, comment)
                VALUES (:entityId, :entityType, :userId, :parentId, :comment)
                RETURNING id
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':entityId', $itemStockId);
            $stmt->bindValue(':entityType', $data['entity_type'] ?? null);
            $stmt->bindValue(':userId', $data['user_id'] ?? null);
            $stmt->bindValue(':parentId', $data['parent_id'] ?? null);
            $stmt->bindValue(':comment', $data['comment'] ?? null);

            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Failed to comment on item history");
        }
    }


    public function getGallery()
    {
        $this->authorizeRequest();

        $images = $this->mediaHandler->getImagesFromFolder('starters-gallery');
        if ($images) {
            $this->sendResponse('success', 200, $images);
        } else {
            $this->sendResponse('Failed to retrieve images', 500);
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

        $response = ['message' => $message];

        $formatNumber = function ($value) {
            if (is_numeric($value)) {
                return number_format($value, 2, '.', ',');
            }
            return $value;
        };

        // Process data array
        if (!empty($data)) {
            $processedData = $this->removeNullFields($data);

            // Recursive function to process nested arrays
            $processArray = function ($item) use ($formatNumber, &$processArray) {
                if (is_array($item)) {
                    $result = [];
                    foreach ($item as $key => $value) {
                        $result[$key] = is_array($value) ? $processArray($value) : $value;

                        if (in_array($key, [
                            'price', 'total', 'amount','total_amount',
                            'delivery_charge', 'total_income', 'total_expenses',
                            'total_sales', 'total_purchase', 'total_purchases',
                            'cash_flow', 'balance', 'prev_month_cash_flow',
                            'estimated_cash_flow', 'total_revenue', 'buying_price',
                            'vendor_balance', 'customer_balance', 'total_balance',
                            'shipping_charge', 'total_cost', 'total_profit',
                            'total_transaction', 'expenses', 'highest_revenue', 'highest_expense',

                        ]) && is_numeric($value)) {
                            $formattedKey = 'formatted_' . $key;
                            $result[$formattedKey] = "₦{$formatNumber($value)}";
                        }
                    }
                    return $result;
                }
                return $item;
            };

            $response['data'] = is_array($processedData) ? $processArray($processedData) : $processedData;
        } else {
            $response['data'] = [];
        }

        // Process meta array
        if (!empty($meta)) {
            $response['meta'] = $this->removeNullFields($meta);
        }

        echo json_encode($response);
        exit;
    }

    private function removeNullFields($input)
    {
        if (is_array($input)) {
            return array_filter(array_map([$this, 'removeNullFields'], $input), function ($value) {
                return $value !== null;
            });
        } elseif (is_object($input)) {
            foreach ($input as $key => $value) {
                if ($value === null) {
                    unset($input->$key);
                } else {
                    $input->$key = $this->removeNullFields($value);
                }
            }
        }

        return $input !== null ? $input : '';
    }

    public function search()
    {
        $this->authorizeRequest();

        $filters = [
            'search' => isset($_GET['query']) ? $_GET['query'] : null,
            'page' => isset($_GET['page']) ? $_GET['page'] : 1,
            'page_size' => isset($_GET['page_size']) ? $_GET['page_size'] : 10,
        ];

        $data = $this->search->globalSearch(array_filter($filters));

        if (!empty($data['data'])) {
            $this->sendResponse('success', 200, $data['data'], $data['meta']);
        } else {
            $this->sendResponse('data not found', 200);
        }
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

        $query = "
            SELECT * FROM notifications WHERE user_id = :user_id
            ORDER BY created_at DESC
            LIMIT :page_size OFFSET :offset
        ";
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
        return str_replace('_', ' ', $status);
    }

    public static function getUserByRole($roleNames)
    {
        $db = Database::getInstance()->getConnection();

        if (!is_array($roleNames)) {
            $roleNames = [$roleNames];
        }

        $roleQuery = "SELECT id FROM roles WHERE name IN (" . implode(',', array_fill(0, count($roleNames), '?')) . ")";
        $stmtRole = $db->prepare($roleQuery);
        $stmtRole->execute($roleNames);

        $roleIds = $stmtRole->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($roleIds)) {
            return null;
        }

        $userQuery = "SELECT id FROM users WHERE role_id IN (" . implode(',', array_fill(0, count($roleIds), '?')) . ")";
        $stmtUser = $db->prepare($userQuery);
        $stmtUser->execute($roleIds);

        return $stmtUser->fetchAll(\PDO::FETCH_ASSOC);
    }


    protected function findRecord(string $table, string $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM $table WHERE id = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            throw new \Exception("Record with ID '$id' not found.");
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

    public function sendInvoiceEmail($id, $type, $attachment = null)
    {
        $invoiceData = $this->getInvoiceData($id, $type);

        if (!$invoiceData) {
            throw new \Exception("Invalid record type '$type'.");
        }

        $templateVariables = array_merge([
            "orgAddress" => trim(getenv('ORG_ADDRESS'), '"'),
            "orgName" => trim(getenv('ORG_NAME'), '"'),
            "orgName" => trim(getenv('ORG_NAME'), '"'),
            "orgEmail" => trim(getenv('ORG_EMAIL'), '"'),
            "orgPhone" => trim(getenv('ORG_PHONE'), '"'),
            "orgWebsite" => trim(getenv('ORG_WEBSITE'), '"'),
        ], $invoiceData);

        try {
            $this->emailService->sendInvoice(
                $invoiceData['billedToEmail'],
                $invoiceData['billedTo'],
                $templateVariables,
                $attachment
            );

        } catch (\Exception $e) {
            error_log("Error sending email: " . $e->getMessage());
            return $this->sendResponse('Failed to send Invoice', 500);
        }

        return $this->sendResponse('Invoice sent successfully', 200);
    }

    private function getInvoiceData($id, $type)
    {
        if ($type === 'sales_orders') {
            $invoice = (new Sale())->getInvoiceDetails($id);
            return $this->mapInvoiceData($invoice, 'customer');
        }

        if ($type === 'purchase_orders') {
            $invoice = (new Purchase())->getInvoiceDetails($id);
            return $this->mapInvoiceData($invoice, 'vendor');
        }

        return null;
    }

    public function getInvoiceTotal($id, $type)
    {
        $query = match ($type) {
            'sales_orders' => "SELECT total FROM sales_orders WHERE id = :id",
            'purchase_orders' => "SELECT total FROM purchase_orders WHERE id = :id",
            default => null,
        };

        if (!$query) {
            return 0;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    private function mapInvoiceData($invoice, $prefix)
    {
        return [
            "invoiceNumber" => $invoice['invoice_number'] ?? "",
            "reference" => $invoice['reference_number'] ?? "",
            "issueDate" => $invoice[$prefix === 'customer' ? 'invoice_date' : 'order_date'] ?? "",
            "dueDate" => $invoice['delivery_date'] ?? "",
            "billedTo" => $invoice["{$prefix}_name"] ?? "",
            "billedToAddress" => $invoice["{$prefix}_address"] ?? "",
            "billedToEmail" => $invoice["{$prefix}_email"] ?? "",
            "billedToPhone" => $invoice["{$prefix}_phone"] ?? "",
            "balanceDue" => $invoice["{$prefix}_balance"] ?? "",
            "discount" => $invoice['discount'] ?? "",
            "shipping" => $invoice[$prefix === 'customer' ? 'delivery_charge' : 'shipping_charge'] ?? "",
            "total" => $invoice['total'] ?? "",
            "items" => $invoice['items'] ?? [],
            "notes" => $invoice[$prefix === 'customer' ? 'customer_note' : 'notes'] ?? "",
        ];
    }

    public function shareInvoice($id)
    {
        $type = $_GET['type'] ?? null;

        $invoiceData = $this->getInvoiceData($id, $type);

        if (!$invoiceData) {
            throw new \Exception("Invalid record type '$type'.");
        }

        $this->sendResponse('success', 200, $invoiceData);
    }

    protected function fetchData(
        string $table,
        array $searchColumns = [],
        array $columns = ['*']
    ) {
        try {
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
                return $this->sendResponse('Success', 200, [], []);
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
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return ['data' => [], 'meta' => []];
        }
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
            throw new \Exception("Failed to insert record.");
        }
    }

    protected function deleteData(string $table)
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();
        $ids = isset($data['ids']) ? (array) $data['ids'] : [];

        if (empty($ids)) {
            throw new \Exception("No record ID provided.");
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));

        $query = "DELETE FROM $table WHERE id IN ($placeholders)";
        $stmt = $this->db->prepare($query);

        if ($stmt->execute($ids)) {
            return $this->sendResponse('success', 200, 'Record successfully deleted');
        } else {
            throw new \Exception("Failed to delete record.");
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

    public function deleteRole()
    {
        return $this->deleteData('roles');
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

    public function deleteUnit()
    {
        return $this->deleteData('units');
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

    public function deleteVendorCategory()
    {
        return $this->deleteData('vendor_categories');
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

    public function deleteCurrency()
    {
        return $this->deleteData('currencies');
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

    public function deletePaymentMethod()
    {
        return $this->deleteData('payment_methods');
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

    public function deletePaymentTerm()
    {
        return $this->deleteData('payment_terms');
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

    public function deleteTax()
    {
        return $this->deleteData('taxes');
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

    public function deleteDepartment()
    {
        return $this->deleteData('departments');
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

    public function deleteBranch()
    {
        return $this->deleteData('branches');
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

    public function deleteItemCategory()
    {
        return $this->deleteData('item_categories');
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

    public function deleteItemManufacturer()
    {
        return $this->deleteData('item_manufacturers');
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

    public function deleteBasePayType()
    {
        return $this->deleteData('base_pay_types');
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

    public function deleteNoOfWorkingDays()
    {
        return $this->deleteData('no_of_working_days');
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

    public function deletePermission()
    {
        return $this->deleteData('permissions');
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

    public function deleteExpensesCategory()
    {
        return $this->deleteData('expenses_categories');
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

    public function deleteWorkLeaveQualification()
    {
        return $this->deleteData('work_leave_qualifications');
    }

    public function createDeliveryCharge()
    {
        $data = [
            'name' => $_POST['name'] ?? null,
            'amount' => $_POST['amount'] ?? null,
            'description' => $_POST['description'] ?? null,
        ];

        if (!$data['name'] || !$data['amount']) {
            return $this->sendResponse('error', 400, 'Name and amount are required.');
        }

        return $this->insertData('delivery_charges', $data);
    }

    public function getDeliveryCharges()
    {
        $result = $this->fetchData(
            'delivery_charges',
            ['name', 'amount'],
            ['id', 'name', 'amount'],
        );

        return $this->sendResponse(
            'success',
            200,
            $result['data'],
            $result['meta']
        );
    }

    public function deleteDeliveryCharge()
    {
        return $this->deleteData('delivery_charges');
    }
}
