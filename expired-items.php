<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/database/Database.php';
require_once __DIR__ . '/controllers/BaseController.php';
require_once __DIR__ . '/services/NotificationService.php';
require_once __DIR__ . '/loadenv.php';

// Use the required namespaces
use Database\Database;
use Controllers\BaseController;
use Services\NotificationService;

// Get the singleton instance and PDO connection
$pdo = Database::getNewConnection();

$sql = "
    SELECT s.id, s.item_id, s.expiry_date, s.quantity, 
           i.name AS item_name, u.abbreviation AS unit
    FROM item_stocks s
	JOIN items i ON s.item_id = i.id
	JOIN units u ON i.unit_id = u.id
    WHERE s.expiry_date <= CURRENT_DATE + INTERVAL '7 days'
    AND s.expiry_date > CURRENT_DATE
    ORDER BY s.expiry_date ASC
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $expiringStocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $affectedRows = count($expiringStocks);

    if ($affectedRows > 0) {
        $logMessage = "[" . date('Y-m-d H:i:s') . "] Found $affectedRows items nearing expiry." . PHP_EOL;
        echo $logMessage;

        $usersToNotify = BaseController::getUserByRole(['Admin']);

        if (empty($usersToNotify)) {
            throw new Exception("Admin user not found for notification.");
        }

        // Send notifications for each expiring stock item
        $notificationService = new NotificationService();
        foreach ($expiringStocks as $stock) {
            foreach ($usersToNotify as $userToNotify) {
                if (!isset($userToNotify['id'])) {
                    continue;
                }

                $notification = [
                    'user_id' => $userToNotify['id'],
                    'event' => 'notification',
                    'entity_id' => $stock['id'],
                    'entity_type' => 'item_stocks',
                    'title' => 'Stock Expiry Alert',
                    'body' => "{$stock['quantity']} {$stock['item_name']} set to expire on 
                            {$stock['expiry_date']}. Please review and take necessary action.",
                ];

                $notificationService->sendNotification($notification);
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
