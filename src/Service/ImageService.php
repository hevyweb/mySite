<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\File;

class ImageService
{
    public function sanitize(File $file)
    {
        $imagick = new \Imagick($file->getRealPath());
        $imagick->setImageFormat("jpg");
        $imagick->stripImage();
        $imagick->writeImage($file->getRealPath());
    }
}