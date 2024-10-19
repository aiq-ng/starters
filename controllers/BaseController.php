<?php

namespace Controllers;

class BaseController
{
    protected function getRequestData()
    {
        $json = file_get_contents('php://input');
        return json_decode($json, true);
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

    protected function sendResponse($message, $statusCode, $additionalData = [])
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        $response = [
            'message' => $message,
        ];

        if (!empty($additionalData)) {
            $response['data'] = $additionalData;
        }

        echo json_encode($response);
        exit;
    }
}
