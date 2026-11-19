<?php

namespace App\Service\FileSystem;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileManagerInterface
{
    public function save(UploadedFile $file, string $destination): string;

    public function copy(string $fileName, string $oldDestination, string $newDestination): string;

    public function remove(string $fileName, ?string $destination = null): void;

    public function checkAndCreateFolder(string $dir): void;
    
    public function saveFileContents(string $contents, string $destination, string $fileName): void;
}
