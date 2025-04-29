<?php

namespace App\Service\FileSystem;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileManagementInterface
{
    public function save(UploadedFile $file, string $destination): string;

    public function copy(string $fileName, string $oldDestination, string $newDestination): string;

    public function delete(string $fileName, string $destination): void;
}
