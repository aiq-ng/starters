<?php

namespace Services;

use Database\Database;

class NotificationService
{
    private HttpClientService $httpClientService;
    private $emailService;
    private $db;

    public function __construct()
    {
        $this->httpClientService = new HttpClientService();
        $this->db = Database::getNewConnection();
        $this->emailService = new EmailService();
    }

    public function sendNotification(array $data, $save = true)
    {
        error_log("Sending notification to user {$data["user_id"]} via HTTP");

        error_log("Notification data: " . json_encode($data));

        $url = '/send-notification/';
        $responseData = $this->httpClientService->post(
            $url,
            [
                'Content-Type' => 'application/json',
                'Content-Length' => strlen(json_encode($data))
            ],
            json_encode($data)
        );

        if ($save) {
            $this->saveNotificationToDatabase($data);
        }

        return $responseData;
    }

    public function sendEmailNotification(array $data)
    {
        $templateVariables = [
            'title' => $data['title'],
            'body' => $data['body'],
        ];

        $this->emailService->sendEmailNotification(
            'notification',
            $data['email'],
            $data['name'],
            $templateVariables
        );
    }

    private function saveNotificationToDatabase(array $data)
    {
        $query = "INSERT INTO notifications (user_id, entity_id, entity_type, title, body) 
                  VALUES (:user_id, :entity_id, :entity_type, :title, :body)";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':user_id', $data['user_id'] ?? null, \PDO::PARAM_STR);
        $stmt->bindValue(':entity_id', $data['entity_id'] ?? null, \PDO::PARAM_STR);
        $stmt->bindValue(':entity_type', $data['entity_type'] ?? null, \PDO::PARAM_STR);
        $stmt->bindValue(':title', $data['title'] ?? null, \PDO::PARAM_STR);
        $stmt->bindValue(':body', $data['body'] ?? null, \PDO::PARAM_STR);

        $stmt->execute();
    }
}
