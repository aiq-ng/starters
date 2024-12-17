<?php

namespace Services;

require_once __DIR__ . '/../vendor/autoload.php';

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class MediaHandler
{
    private $upload;

    public function __construct()
    {
        Configuration::instance(getenv('CLOUDINARY_URL'));
        $this->upload = new UploadApi();
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
        return isset($file) && is_uploaded_file($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK;
    }
}
