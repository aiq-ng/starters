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
            'invoice' => getenv('INVOICE_TEMPLATE_ID'),
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

    public function sendInvoice($recipientEmail, $recipientName, $templateVariables, $attachment = null)
    {
        $processedAttachment = $attachment ? $this->processAttachment($attachment) : null;

        return $this->sendEmail(
            $this->templates->invoice,
            $recipientEmail,
            $recipientName,
            $templateVariables,
            $processedAttachment
        );
    }

    public function sendEmailNotification($recipientEmail, $recipientName, $templateVariables)
    {
        return $this->sendEmail(
            $this->templates->emailNotification,
            $recipientEmail,
            $recipientName,
            $templateVariables
        );
    }

    private function processAttachment($attachment)
    {
        if (empty($attachment['tmp_name']) || empty($attachment['name']) || empty($attachment['type'])) {
            throw new \Exception('Invalid attachment details.');
        }

        $fileData = file_get_contents($attachment['tmp_name']);
        if ($fileData === false) {
            throw new \Exception('Failed to read file content.');
        }

        return [
            'file_name' => $attachment['name'],
            'content' => base64_encode($fileData),
            'content_type' => $attachment['type'],
        ];
    }

    private function sendEmail(
        $templateKey,
        $recipientEmail,
        $recipientName = '',
        $mergeInfo = [],
        $attachment = null
    ) {
        $payload = [
            'mail_template_key' => $templateKey,
            'from' => [
                'address' => 'noreply@hordun.software',
                'name' => 'starters',
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
        ];

        if ($attachment) {
            $payload['attachments'] = [$attachment];
        }

        $payload = json_encode($payload);

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
