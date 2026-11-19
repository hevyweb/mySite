<?php

namespace App\Service;

class ImageService
{
    public function __construct(
        private readonly string $env,
    ) {
    }

    /**
     * @throws \ImagickException
     */
    public function thumbnail(
        string $imagePath,
        int $width,
        int $height,
        bool $resize = true,
    ): string {
        if ('test' === $this->env) {
            return $imagePath;
        }

        $imagick = new \Imagick($imagePath);

        $origWidth = $imagick->getImageWidth();
        $origHeight = $imagick->getImageHeight();

        if ($origWidth <= $width && $origHeight <= $height) {
            return $imagick->getImageBlob();
        }

        $targetWidth = min($origWidth, $width);
        $targetHeight = min($origHeight, $height);

        if ($resize) {
            $this->applyResizeAndCrop($imagick, $origWidth, $origHeight, $targetWidth, $targetHeight);
        } else {
            $imagick->scaleImage($targetWidth, $targetHeight, true);
        }

        return $imagick->getImageBlob();
    }

    private function applyResizeAndCrop(
        \Imagick $imagick,
        int $origWidth,
        int $origHeight,
        int $targetWidth,
        int $targetHeight,
    ): void {
        [$scaleWidth, $scaleHeight] = $this->calculateScaleDimensions(
            $origWidth,
            $origHeight,
            $targetWidth,
            $targetHeight
        );

        $imagick->resizeImage($scaleWidth, $scaleHeight, \Imagick::FILTER_LANCZOS, 1);

        $cropX = (int) max(0, round(($scaleWidth - $targetWidth) / 2));
        $cropY = (int) max(0, round(($scaleHeight - $targetHeight) / 2));

        $imagick->cropImage($targetWidth, $targetHeight, $cropX, $cropY);
        $imagick->setImagePage(0, 0, 0, 0);
    }

    /**
     * @return array{int, int}
     */
    public function calculateScaleDimensions(
        int $origWidth,
        int $origHeight,
        int $targetWidth,
        int $targetHeight,
    ): array {
        $origRatio = $origWidth / $origHeight;
        $targetRatio = $targetWidth / $targetHeight;

        if ($origRatio > $targetRatio) {
            return [
                (int) round($targetHeight * $origRatio),
                $targetHeight,
            ];
        }

        return [
            $targetWidth,
            (int) round($targetWidth / $origRatio),
        ];
    }
}
