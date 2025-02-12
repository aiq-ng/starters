<?php

namespace Services;

require_once __DIR__ . '/../vendor/autoload.php';

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use GuzzleHttp\Client;

class MediaHandler
{
    private $upload;
    private $httpClient;

    public function __construct()
    {
        Configuration::instance(getenv('CLOUDINARY_URL'));
        $this->upload = new UploadApi();
        $this->httpClient = new Client();
    }

    public function handleMediaFiles($files)
    {
        $mediaLinks = [];

        try {
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                if ($this->isValidFile($file)) {
                    $uploadedFileUrl = $this->upload->upload($file['tmp_name'], [
                        'resource_type' => 'auto',
                        'folder' => 'media',
                    ]);

                    $mediaLinks[] = $uploadedFileUrl['secure_url'];
                } else {
                    error_log('Invalid file provided for upload: ' . $file['name']);
                }
            }

            return !empty($mediaLinks) ? $mediaLinks : false;
        } catch (\Exception $e) {
            error_log('Error uploading media files: ' . $e->getMessage());
            return false;
        }
    }

    private function isValidFile($file)
    {
        return isset($file['tmp_name']) && file_exists($file['tmp_name']) && is_readable($file['tmp_name']);
    }

    public function getImagesFromFolder($folder = 'starters-gallery')
    {
        try {
            $cloudName = getenv('CLOUDINARY_CLOUD_NAME');
            $apiKey = getenv('CLOUDINARY_API_KEY');
            $apiSecret = getenv('CLOUDINARY_API_SECRET');

            if (!$cloudName || !$apiKey || !$apiSecret) {
                error_log('Cloudinary credentials are missing.');
                return false;
            }

            $url = "https://api.cloudinary.com/v1_1/{$cloudName}/resources/search";

            $auth = base64_encode("{$apiKey}:{$apiSecret}");

            $body = json_encode([
                'expression' => "folder:{$folder}",
                'max_results' => 100,
            ]);

            $response = $this->httpClient->post($url, [
                'headers' => [
                    'Authorization' => "Basic {$auth}",
                    'Content-Type' => 'application/json',
                ],
                'body' => $body,
            ]);

            $responseBody = json_decode($response->getBody(), true);

            if (isset($responseBody['error'])) {
                error_log('Cloudinary API error: ' . $responseBody['error']['message']);
                return false;
            }

            if (!empty($responseBody['resources'])) {
                return array_map(fn ($resource) => $resource['secure_url'], $responseBody['resources']);
            } else {
                error_log("No images found in folder: {$folder}");
                return [];
            }

        } catch (\Exception $e) {
            error_log('Error retrieving images from Cloudinary: ' . $e->getMessage());
            return false;
        }
    }
}
