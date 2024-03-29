<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\File;

class ImageService
{
    /**
     * @throws \ImagickException
     */
    public function sanitize(File $file): void
    {
        $imagick = new \Imagick($file->getRealPath());
        $imagick->setImageFormat('jpg');
        $imagick->stripImage();
        $imagick->writeImage($file->getRealPath());
    }
}
