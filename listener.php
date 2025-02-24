<?php

require_once __DIR__ . '/database/Database.php';
require_once __DIR__ . '/loadenv.php';

// Use the required namespaces
use Database\Database;
use Controllers\BaseController;
use Services\NotificationService;

// Get the singleton instance and PDO connection
$database = Database::getInstance();
$pdo = $database->getConnection();

$sql = "
    UPDATE purchase_orders
    SET status = 'overdue',
        updated_at = CURRENT_TIMESTAMP
    WHERE payment_due_date < CURRENT_DATE
    AND status NOT IN ('paid', 'cancelled', 'overdue')
    RETURNING id, reference_number
";

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $updatedPurchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $affectedRows = count($updatedPurchases);

    if ($affectedRows > 0) {
        $logMessage = "[" . date('Y-m-d H:i:s') . "] Updated $affectedRows purchase orders to 'overdue'" . PHP_EOL;
        echo $logMessage;

        $usersToNotify = BaseController::getUserByRole(['Admin']);

        if (empty($usersToNotify)) {
            throw new Exception("Admin user not found for notification.");
        }

        // Send notifications for each updated purchase order
        $notificationService = new NotificationService();
        foreach ($updatedPurchases as $purchase) {
            foreach ($usersToNotify as $userToNotify) {
                if (!isset($userToNotify['id'])) {
                    continue;
                }

                $notification = [
                    'user_id' => $userToNotify['id'],
                    'event' => 'notification',
                    'entity_id' => $purchase['id'],
                    'entity_type' => 'purchase_orders',
                    'title' => 'Purchase Order Overdue',
                    'body' => "Purchase order {$purchase['reference_number']} is overdue.",
                ];

                $notificationService->sendNotification($notification);
            }
        }
    }

    $pdo->commit();

} catch (PDOException $e) {
    $pdo->rollBack();
    $errorMessage = "[" . date('Y-m-d H:i:s') . "] PDO Error updating purchase orders: " . $e->getMessage() . PHP_EOL;
    echo $errorMessage;
    exit(1);
} catch (Exception $e) {
    $pdo->rollBack();
    $errorMessage = "[" . date('Y-m-d H:i:s') . "] General Error: " . $e->getMessage() . PHP_EOL;
    echo $errorMessage;
    exit(1);
}
