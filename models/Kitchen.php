<?php

namespace Models;

use Database\Database;
use Services\NotificationService;
use Controllers\BaseController;

class Kitchen
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();

    }

    public function getChefs($roleId)
    {
        $query = "SELECT u.id, u.name, COUNT(ca.order_id) AS total_assignments 
              FROM users u
              LEFT JOIN chef_assignments ca ON u.id = ca.chef_id
              WHERE u.role_id = :role_id
              GROUP BY u.id, u.name";

        $stmt = $this->db->prepare($query);

        try {
            $stmt->bindValue(':role_id', $roleId);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch chefs: " . $e->getMessage());
        }
    }

    public function getRiders($roleId)
    {
        $query = "SELECT u.id, u.name, COUNT(da.order_id) AS total_assignments 
              FROM users u
              LEFT JOIN driver_assignments da ON u.id = da.driver_id
              WHERE u.role_id = :role_id
              GROUP BY u.id, u.name";

        $stmt = $this->db->prepare($query);

        try {
            $stmt->bindValue(':role_id', $roleId);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch drivers: " . $e->getMessage());
        }
    }


    public function getNewOrders($filters = [])
    {
        $page = $filters['page'] ?? 1;
        $pageSize = $filters['page_size'] ?? 10;
        $status = $filters['status'] ?? null;
        $date = $filters['date'] ?? date('Y-m-d');
        $search = $filters['search'] ?? null;
        $orderType = $filters['order_type'] ?? null;
        $sortBy = $filters['sort_by'] ?? 'delivery_date';
        $order = $filters['order'] ?? 'DESC';
        $sentToKitchen = $filters['sent_to_kitchen'] ?? null;

        $offset = ($page - 1) * $pageSize;

        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $allowedSortColumns = ['created_at', 'delivery_date', 'order_title', 'status'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        $query = "
            SELECT so.id,
                so.order_id,
                so.order_title,
                so.processed_by AS sales_rep_id,
		        u.name AS sales_rep_name,
                c.display_name AS customer_name,
                d.name AS driver_name,
                so.order_type,
                so.created_at AS arrival_time,
                CONCAT(so.delivery_date, ' ', so.delivery_time) AS delivery_time,
                so.status,
                COALESCE(SUM(soi.quantity * soi.price), 0) AS total_amount,
                json_agg(
                    json_build_object(
                        'item_id', p.id,
                        'item_name', p.item_details,
                        'quantity', soi.quantity,
                        'amount', soi.quantity * soi.price
                    )
		) AS items,
		so.created_at
        FROM sales_orders so
        LEFT JOIN customers c ON so.customer_id = c.id
        LEFT JOIN sales_order_items soi ON soi.sales_order_id = so.id
	    LEFT JOIN price_lists p ON soi.item_id = p.id
        LEFT JOIN users u ON so.processed_by = u.id
        LEFT JOIN driver_assignments da ON so.id = da.order_id
        LEFT JOIN users d ON da.driver_id = d.id
            WHERE 1=1
        ";

        $params = [];
        $conditions = [];

        if ($status && strtolower($status) !== 'all') {
            $conditions[] = "so.status = :status";
            $params[':status'] = $status;
        } else {
            $conditions[] = "so.status IN ('cancelled', 'new order', 'in progress', 'in delivery', 'delivered')";
        }


        if ($search) {
            $conditions[] = "(so.order_title ILIKE :search OR so.reference_number ILIKE :search OR c.display_name ILIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if ($orderType) {
            $conditions[] = "so.order_type = :order_type";
            $params[':order_type'] = $orderType;
        }

        if ($sentToKitchen !== null) {
            $conditions[] = "so.sent_to_kitchen = :sent_to_kitchen";
            $params[':sent_to_kitchen'] = $sentToKitchen ? 'true' : 'false';
        }

        // Append conditions to query
        if (count($conditions) > 0) {
            $query .= " AND " . implode(' AND ', $conditions);
        }

        $query .= "
            GROUP BY so.id, c.display_name, c.email, so.order_id, so.order_title,
                    so.order_type, so.discount, so.delivery_charge, so.total, 
                    so.created_at, so.delivery_date, so.status, so.processed_by, u.name, d.name
            ORDER BY so.$sortBy $order
            LIMIT :page_size OFFSET :offset
        ";

        $totalItems = $this->getCount($conditions, $params);

        // Add pagination parameters
        $params[':page_size'] = $pageSize;
        $params[':offset'] = $offset;

        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute($params);
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as &$row) {
                $row['items'] = json_decode($row['items'], true);
            }

            $meta = [
                'total_data' => (int) $totalItems,
                'total_pages' => ceil($totalItems / $pageSize),
                'page_size' => (int) $pageSize,
                'previous_page' => $page > 1 ? (int) $page - 1 : null,
                'current_page' => (int) $page,
                'next_page' => (int) $page + 1,
            ];

            return [
                'data' => $result,
                'meta' => $meta,
            ];

        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch orders: " . $e->getMessage());
        }
    }

    public function getCount($conditions, $params)
    {
        $countQuery = "
            SELECT COUNT(DISTINCT so.id)
            FROM sales_orders so
            LEFT JOIN customers c ON so.customer_id = c.id
            LEFT JOIN sales_order_items soi ON soi.sales_order_id = so.id
            LEFT JOIN price_lists p ON soi.item_id = p.id
            WHERE 1=1
        ";

        if (count($conditions) > 0) {
            $countQuery .= " AND " . implode(' AND ', $conditions);
        }

        $stmt = $this->db->prepare($countQuery);

        try {
            $stmt->execute($params);
            $count = $stmt->fetchColumn();
            return $count;
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch orders count: " . $e->getMessage());
        }
    }

    public function updateOrderStatus($id, $status)
    {
        $query = "UPDATE sales_orders SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute([
                ':id' => $id,
                ':status' => $status,
            ]);

            $usersToNotify = BaseController::getUserByRole('Admin');

            if (empty($usersToNotify)) {
                throw new \Exception("No Admin user found for notification.");
            }

            foreach ($usersToNotify as $userToNotify) {
                if (!isset($userToNotify['id'])) {
                    continue;
                }

                $notification = [
                    'user_id' => $userToNotify['id'],
                    'event' => 'status_update',
                    'entity_type' => 'sales_order',
                    'title' => 'Order Status Update',
                    'body' => 'Order status has been updated to ' . $status,
                    'event_data' => [
                        'order_id' => $id,
                        'status' => $status,
                    ],
                ];

                (new NotificationService())->sendNotification($notification, false);
            }


        } catch (\Exception $e) {
            throw new \Exception("Failed to update order status: " . $e->getMessage());
        }
    }

    public function assignOrder($orderId, $userId)
    {
        $query = "INSERT INTO driver_assignments (order_id, driver_id) VALUES (:order_id, :driver_id)";

        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute([
                ':order_id' => $orderId,
                ':driver_id' => $userId,
            ]);
        } catch (\Exception $e) {
            throw new \Exception("Failed to assign order: " . $e->getMessage());
        }
    }

    public function getOrders($driverId = null, $status = null, $page = 1, $pageSize = 100)
    {
        $offset = ($page - 1) * $pageSize;

        $conditions = ["so.id IS NOT NULL"];
        $params = [];

        if ($driverId) {
            $conditions[] = "da.driver_id = :driver_id";
            $params[':driver_id'] = $driverId;
        }

        if ($status) {
            $conditions[] = "so.status = :status";
            $params[':status'] = $status;
        }

        $conditionString = "WHERE " . implode(" AND ", $conditions);

        $countQuery = "
            SELECT COUNT(DISTINCT so.id) AS total_items
            FROM sales_orders so
            LEFT JOIN driver_assignments da ON da.order_id = so.id
            {$conditionString}
        ";

        $countStmt = $this->db->prepare($countQuery);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $countStmt->execute();
        $totalItems = $countStmt->fetchColumn();

        $query = "
            SELECT so.id,
                so.order_id,
                u.id AS driver_id,
                u.name AS driver_name,
                so.delivery_time,
                json_agg(
                   json_build_object(
                       'item_id', p.id,
                       'item_name', p.item_details,
                       'quantity', soi.quantity
                   )
                ) AS items
            FROM sales_orders so
            LEFT JOIN driver_assignments da ON da.order_id = so.id
            LEFT JOIN sales_order_items soi ON soi.sales_order_id = so.id
            LEFT JOIN price_lists p ON soi.item_id = p.id
            LEFT JOIN users u ON da.driver_id = u.id
            {$conditionString}
            GROUP BY so.id, so.order_id, so.delivery_date, so.delivery_time, 
                     u.name, u.id
            ORDER BY so.created_at DESC
            LIMIT :page_size OFFSET :offset
        ";

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->bindValue(':page_size', $pageSize, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

        try {
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as &$row) {
                $row['items'] = json_decode($row['items'], true);
            }

            $meta = [
                'total_data' => (int) $totalItems,
                'total_pages' => ceil($totalItems / $pageSize),
                'page_size' => (int) $pageSize,
                'previous_page' => $page > 1 ? $page - 1 : null,
                'current_page' => (int) $page,
                'next_page' => $page * $pageSize < $totalItems ? $page + 1 : null,
            ];

            return [
                'data' => $result,
                'meta' => $meta
            ];
        } catch (\Exception $e) {
            error_log("Failed to fetch orders: " . $e->getMessage());
            throw new \Exception("Failed to fetch orders.");
        }
    }
}
