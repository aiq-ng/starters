<?php

namespace Services;

require_once __DIR__ . '/../vendor/autoload.php';


use Database\Database;
use BarcodeBakery\Common\BCGFontFile;
use BarcodeBakery\Common\BCGColor;
use BarcodeBakery\Common\BCGDrawing;
use BarcodeBakery\Barcode\BCGcode39;

class BarcodeService
{
    private $mediaHandler;
    private $db;

    public function __construct()
    {
        $this->mediaHandler = new MediaHandler();
        $this->db = Database::getInstance()->getConnection();
    }

    public function generateBarcode(string $sku): string
    {
        try {
            $tempDir = __DIR__ . "/../temp";
            if (!is_dir($tempDir) && !mkdir($tempDir, 0777, true)) {
                throw new \Exception('Failed to create temp directory.');
            }

            $barcodeFileName = $tempDir . "/{$sku}.png";

            // Define colors
            $colorBlack = new BCGColor(0, 0, 0);
            $colorWhite = new BCGColor(255, 255, 255);

            // Create barcode
            $code = new BCGcode39();
            $code->setScale(2);
            $code->setThickness(30);
            $code->setForegroundColor($colorBlack);
            $code->setBackgroundColor($colorWhite);
            $code->parse($sku);
            $code->setLabel('');

            $drawing = new BCGDrawing($code, $colorWhite);
            $drawing->finish(BCGDrawing::IMG_FORMAT_PNG, $barcodeFileName);

            $mediaLinks = $this->mediaHandler->handleMediaFiles([
                ['tmp_name' => $barcodeFileName, 'name' => $sku . '.png']
            ]);

            unlink($barcodeFileName);

            if (empty($mediaLinks)) {
                throw new \Exception('Failed to upload barcode image to cloud.');
            }

            error_log('Barcode uploaded successfully: ' . $mediaLinks[0]);
            return $mediaLinks[0];

        } catch (\Exception $e) {
            error_log('Error generating or uploading barcode: ' . $e->getMessage());
            throw new \Exception('An error occurred while generating or uploading the barcode.');
        }
    }

    public function processItems(array $items): array
    {
        foreach ($items as &$item) {
            $item['media'] = !empty($item['media']) ? json_decode($item['media'], true) : null;

            // If barcode is missing, generate and upload it
            if (empty($item['barcode'])) {
                if (!empty($item['sku'])) {
                    $barcodeUrl = $this->generateBarcode($item['sku'], $item['id']);
                    $item['barcode'] = $barcodeUrl;
                } else {
                    throw new \Exception("SKU is missing for item ID: {$item['id']}");
                }
            } else {
                // Decode the barcode URL if it exists
                $item['barcode'] = json_decode($item['barcode'], true);
            }
        }

        return $items;
    }

    public function updateItemBarcodeInDb(string $itemId, string $barcodeUrl)
    {
        try {
            $query = "UPDATE items SET barcode = :barcode WHERE id = :id";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':barcode', json_encode($barcodeUrl), \PDO::PARAM_STR);
            $stmt->bindValue(':id', $itemId, \PDO::PARAM_STR);

            if (!$stmt->execute()) {
                throw new \Exception('Failed to update item barcode.');
            }
        } catch (\PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            throw new \Exception('Database operation failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            error_log('Error: ' . $e->getMessage());
            throw new \Exception('An error occurred while updating the barcode.');
        }
    }
}
