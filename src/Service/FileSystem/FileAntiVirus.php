<?php

namespace App\Service\FileSystem;

class FileAntiVirus implements FileAntiVirusInterface
{
    public function __construct(private string $env)
    {
    }

    /**
     * @throws \ImagickException
     */
    #[\Override]
    public function sanitize(string $fileName, string $destination): void
    {
        if ($this->env === 'test') {
            return;
        }

        $filePath = $destination.DIRECTORY_SEPARATOR.$fileName;
        $imagick = new \Imagick($filePath);
        $imagick->setImageFormat('jpg');
        $imagick->stripImage();
        $imagick->writeImage($filePath);
    }
}
