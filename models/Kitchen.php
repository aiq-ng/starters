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
                c.display_name AS customer_name,
                so.processed_by AS sales_rep,
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
                ) AS items
            FROM sales_orders so
            LEFT JOIN customers c ON so.customer_id = c.id
            LEFT JOIN sales_order_items soi ON soi.sales_order_id = so.id
            LEFT JOIN price_lists p ON soi.item_id = p.id
            WHERE 1=1
        ";

        $params = [];
        $conditions = [];

        if ($status && strtolower($status) !== 'all') {
            $conditions[] = "so.status = :status";
            $params[':status'] = $status;
        } else {
            $conditions[] = "so.status IN ('new order', 'in progress', 'completed')";
        }

        if ($date) {
            $conditions[] = "DATE(so.created_at) = :date";
            $params[':date'] = $date;
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
                    so.created_at, so.delivery_date, so.status, so.processed_by
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

            $userToNotify = BaseController::getUserByRole('Admin');

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

        } catch (\Exception $e) {
            throw new \Exception("Failed to update order status: " . $e->getMessage());
        }
    }

    public function assignOrder($orderId, $userId)
    {
        $query = "INSERT INTO chef_assignments (order_id, chef_id) VALUES (:order_id, :chef_id)";

        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute([
                ':order_id' => $orderId,
                ':chef_id' => $userId,
            ]);
        } catch (\Exception $e) {
            throw new \Exception("Failed to assign order: " . $e->getMessage());
        }
    }

    public function getAssignedOrders($chefId, $page = 1, $pageSize = 100)
    {
        return $this->getChefAssignedOrders($chefId, $page, $pageSize);
    }

    public function getAllAssignedOrders($chefId = null, $page = 1, $pageSize = 100)
    {
        if ($chefId !== null) {
            return $this->getChefAssignedOrders($chefId, $page, $pageSize);
        }
        return $this->getChefAssignedOrders(null, $page, $pageSize);
    }

    public function getChefAssignedOrders($chefId = null, $page = 1, $pageSize = 100)
    {
        $offset = ($page - 1) * $pageSize;

        $condition = $chefId
            ? "WHERE ca.chef_id = :chef_id AND so.id IS NOT NULL"
            : "WHERE so.id IS NOT NULL";

        // Query to get total count before applying pagination
        $countQuery = "
        SELECT COUNT(DISTINCT so.id) AS total_items
        FROM chef_assignments ca
        LEFT JOIN sales_orders so ON so.id = ca.order_id
        {$condition}
    ";

        $countStmt = $this->db->prepare($countQuery);
        if ($chefId) {
            $countStmt->bindValue(':chef_id', $chefId, \PDO::PARAM_INT);
        }
        $countStmt->execute();
        $totalItems = $countStmt->fetchColumn();

        $query = "
            SELECT so.id,
                   so.order_id,
                   u.firstname AS chef_name,
                   so.delivery_time,
                   json_agg(
                       json_build_object(
                           'item_id', p.id,
                           'item_name', p.item_details,
                           'quantity', soi.quantity
                       )
                   ) AS items
            FROM chef_assignments ca
            LEFT JOIN sales_orders so ON so.id = ca.order_id
            LEFT JOIN sales_order_items soi ON soi.sales_order_id = so.id
            LEFT JOIN price_lists p ON soi.item_id = p.id
            LEFT JOIN users u ON ca.chef_id = u.id
            {$condition}
            GROUP BY so.id, so.order_id, so.delivery_date, so.delivery_time, 
                     u.firstname
            ORDER BY so.created_at DESC
            LIMIT :page_size OFFSET :offset
        ";

        $stmt = $this->db->prepare($query);

        try {
            if ($chefId) {
                $stmt->bindValue(':chef_id', $chefId);
            }
            $stmt->bindValue(':page_size', $pageSize);
            $stmt->bindValue(':offset', $offset);

            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as &$row) {
                $row['items'] = json_decode($row['items'], true);
            }

            // Pagination meta
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
            error_log("Failed to fetch assigned orders: " . $e->getMessage());
            throw new \Exception("Failed to fetch assigned orders.");
        }
    }
}
