<?php

namespace Services;

class NotificationService
{
    private HttpClientService $httpClientService;

    public function __construct(HttpClientService $httpClientService)
    {
        $this->httpClientService = $httpClientService;
    }

    public function sendNotification(string $userId, array $data)
    {
        error_log("Sending notification to user $userId via HTTP");

        $url = '/send-notification/';
        $responseData = $this->httpClientService->post(
            $url,
            [
                'Content-Type' => 'application/json',
                'Content-Length' => strlen(json_encode($data))
            ],
            json_encode($data)
        );

        return $responseData;

    }
}
