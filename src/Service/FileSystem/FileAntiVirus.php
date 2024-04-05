<?php

namespace App\Service\FileSystem;

class FileAntiVirus implements FileAntiVirusInterface
{
    /**
     * @throws \ImagickException
     */
    public function sanitize(string $fileName, string $destination): void
    {
        $filePath = $destination.DIRECTORY_SEPARATOR.$fileName;
        $imagick = new \Imagick($filePath);
        $imagick->setImageFormat('jpg');
        $imagick->stripImage();
        $imagick->writeImage($filePath);
    }
}
