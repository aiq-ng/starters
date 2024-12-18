<?php

namespace Services;

require_once __DIR__ . '/../vendor/autoload.php';

class EmailService
{
    private $templates;
    private $authorizationKey;
    private $url;

    public function __construct()
    {
        $this->authorizationKey = trim(getenv('EMAIL_AUTH_KEY'), '"');
        $this->url = trim(getenv('EMAIL_API_URL'), '"');

        $this->templates = (object) [
            'userVerification' => getenv('EMAIL_VERIFICATION_TEMPLATE_ID'),
            'resetPassword' => getenv('RESET_PASSWORD_TEMPLATE_ID'),
            'loginDetails' => getenv('LOGIN_DETAILS_TEMPLATE_ID'),
        ];
    }

    public function sendNewUserVerification($recipientEmail, $recipientName, $templateVariables)
    {
        return $this->sendEmail(
            $this->templates->userVerification,
            $recipientEmail,
            $recipientName,
            $templateVariables
        );
    }

    public function sendResetPasswordVerification($recipientEmail, $recipientName, $templateVariables)
    {
        return $this->sendEmail(
            $this->templates->resetPassword,
            $recipientEmail,
            $recipientName,
            $templateVariables
        );
    }

    public function sendLoginDetails($recipientEmail, $recipientName, $templateVariables)
    {
        return $this->sendEmail(
            $this->templates->loginDetails,
            $recipientEmail,
            $recipientName,
            $templateVariables
        );
    }

    private function sendEmail($templateKey, $recipientEmail, $recipientName, $mergeInfo)
    {
        $payload = json_encode([
            'mail_template_key' => $templateKey,
            'from' => [
                'address' => 'noreply@hordun.software',
                'name' => 'diconline',
            ],
            'to' => [
                [
                    'email_address' => [
                        'address' => $recipientEmail,
                        'name' => $recipientName,
                    ]
                ]
            ],
            'merge_info' => $mergeInfo,
        ]);

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: ' . $this->authorizationKey,
        ];

        $ch = curl_init($this->url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        try {
            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new \Exception('Request Error: ' . curl_error($ch));
            }

            curl_close($ch);
            return json_decode($response, true);
        } catch (\Exception $e) {
            error_log('Email exception: ' . $e->getMessage());
            return false;
        }
    }
}
