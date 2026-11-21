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
    public function sanitize(string $fileName, string $destination): bool
    {
        if ('test' === $this->env) {
            return true;
        }

        $filePath = $destination.DIRECTORY_SEPARATOR.$fileName;
        $imagick = new \Imagick($filePath);
        $imagick->setImageFormat('jpg');
        $imagick->stripImage();
        $imagick->writeImage($filePath);

        return true;
    }
}
