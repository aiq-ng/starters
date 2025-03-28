<?php

namespace Services;

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

class MediaHandler
{
    private $httpClient;
    private $uploadDir;

    public function __construct()
    {
        $this->httpClient = new Client();
        $this->uploadDir = __DIR__ . '/../storage/media/';

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
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
                    $fileName = basename($file['name']);
                    $targetPath = $this->uploadDir . $fileName;

                    if (file_exists($targetPath)) {
                        $mediaLinks[] = $this->getServerFileUrl($fileName);
                        continue;
                    }

                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $mediaLinks[] = $this->getServerFileUrl($fileName);
                    }
                }
            }

            return !empty($mediaLinks) ? $mediaLinks : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function isValidFile($file)
    {
        return isset($file['tmp_name']) && file_exists($file['tmp_name']) && is_readable($file['tmp_name']);
    }

    private function getServerFileUrl($fileName)
    {
        $baseUrl = $_SERVER['HTTP_HOST'] . str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->uploadDir);
        return 'http://' . rtrim($baseUrl, '/') . '/' . $fileName;
    }

    public function getImagesFromFolder($folder = 'storage/media')
    {
        try {
            $folderPath = __DIR__ . "/../$folder/";

            if (!is_dir($folderPath)) {
                return false;
            }

            $files = array_diff(scandir($folderPath), ['.', '..']);

            return empty($files) ? [] : array_map(fn ($file) => $this->getServerFileUrl($file), $files);
        } catch (\Exception $e) {
            return false;
        }
    }
}

