<?php

namespace App\Service;

use Imagick;
use ImagickException;

class ImageService
{
    /**
     * Generates a thumbnail image based on width, height, and cropping parameters.
     *
     * Uses PHP 8.4 new instance method chaining directly without extra parentheses.
     *
     * @throws ImagickException
     */
    public function thumbnail(
        string $imagePath,
        int $width,
        int $height,
        bool $resize = true
    ): string {
        $imagick = new Imagick($imagePath);

        // 1. Get original image dimensions
        $origWidth = $imagick->getImageWidth();
        $origHeight = $imagick->getImageHeight();

        // 2. Safeguard: If the image is smaller than the target in both dimensions, do not resize/crop
        if ($origWidth <= $width && $origHeight <= $height) {
            return $imagick->getImageBlob();
        }

        // 3. Determine target bounds based on whether one side is smaller than target
        $targetWidth = min($origWidth, $width);
        $targetHeight = min($origHeight, $height);

        if ($resize) {
            // BEST FIT RESIZE & CROP

            // Calculate aspect ratios
            $origRatio = $origWidth / $origHeight;
            $targetRatio = $targetWidth / $targetHeight;

            if ($origRatio > $targetRatio) {
                // Image is wider than target frame: match target height, crop left/right
                $scaleHeight = $targetHeight;
                $scaleWidth = (int) round($targetHeight * $origRatio);
            } else {
                // Image is taller than target frame: match target width, crop top/bottom
                $scaleWidth = $targetWidth;
                $scaleHeight = (int) round($targetWidth / $origRatio);
            }

            // Resize image keeping aspect ratio intact
            $imagick->resizeImage($scaleWidth, $scaleHeight, Imagick::FILTER_LANCZOS, 1);

            // Calculate center offsets for cropping
            $cropX = (int) max(0, round(($scaleWidth - $targetWidth) / 2));
            $cropY = (int) max(0, round(($scaleHeight - $targetHeight) / 2));

            // Crop to exact dimensions from center
            $imagick->cropImage($targetWidth, $targetHeight, $cropX, $cropY);
            $imagick->setImagePage(0, 0, 0, 0); // Reset page geometry after crop
        } else {
            // FIT-WITHIN FRAME WITHOUT CROPPING

            // Imagick's scaleImage with bestfit=true respects bounding box without distortion
            $imagick->scaleImage($targetWidth, $targetHeight, true);
        }

        // PHP 8.4 Feature: Method chaining directly on new class instance without parentheses
        return $imagick->getImageBlob();
    }
}