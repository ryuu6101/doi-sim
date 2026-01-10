<?php

namespace App\Services;

use Imagick;
use ImagickException;

class CropImageService
{
    public function chuyenanh($pdfData, $pageNumber, $width, $height, $outputPath) {
        try {
            // Save PDF data to temporary file
            $tempPdfPath = sys_get_temp_dir() . '/' . uniqid('pdf_') . '.pdf';
            file_put_contents($tempPdfPath, $pdfData);
            
            // Render PDF page to image
            $image = $this->getPageImage($pageNumber, $width, $height, $tempPdfPath, 150);
            
            if ($image === false) {
                throw new \Exception('Failed to render PDF page');
            }
            
            // Crop the QR code area (coordinates from original C# code)
            $cropX = 737;
            $cropY = 261;
            $cropWidth = 110;
            $cropHeight = 110;
            
            $croppedImage = $this->cropImage($image, $cropX, $cropY, $cropWidth, $cropHeight);
            
            // Save as JPEG
            imagejpeg($croppedImage, $outputPath, 90);
            
            // Clean up
            imagedestroy($image);
            imagedestroy($croppedImage);
            
            if (file_exists($tempPdfPath)) {
                unlink($tempPdfPath);
            }
            
            return true;
            
        } catch (\Exception $e) {
            throw $e;
            return false;
        }
    }

    private function getPageImage($pageNumber, $width, $height, $pdfPath, $dpi) {
        // Method 1: Using Imagick (recommended)
        if (extension_loaded('imagick')) {
            try {
                $imagick = new Imagick();
                $imagick->setResolution($dpi, $dpi);
                $imagick->readImage($pdfPath . '[' . ($pageNumber - 1) . ']');
                $imagick->setImageFormat('png');
                
                // Resize to desired dimensions
                $imagick->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
                
                // Convert to GD resource
                $imageBlob = $imagick->getImageBlob();
                $image = imagecreatefromstring($imageBlob);
                
                $imagick->clear();
                $imagick->destroy();
                
                return $image;
                
            } catch (ImagickException $e) {
                throw $e;
                return false;
            }
        }
    }

    private function cropImage($originalImage, $x, $y, $width,$height) {
        $croppedImage = imagecreatetruecolor($width, $height);
        
        // Copy cropped area from original image
        imagecopy(
            $croppedImage,      // Destination
            $originalImage,     // Source
            0,                  // Destination X
            0,                  // Destination Y
            $x,                 // Source X
            $y,                 // Source Y
            $width,             // Width
            $height             // Height
        );
        
        return $croppedImage;
    }
}