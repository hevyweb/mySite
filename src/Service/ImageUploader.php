<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\FileBag;

interface ImageUploader
{
    public function save(FileBag $fileBag, string $destination): File;

    public function delete(string $fileName, string $destination): bool;
}