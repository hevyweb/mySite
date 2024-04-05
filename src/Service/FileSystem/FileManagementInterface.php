<?php

namespace App\Service\FileSystem;

use Symfony\Component\HttpFoundation\FileBag;

interface FileManagementInterface
{
    public function save(FileBag $fileBag, string $destination): string;

    public function copy(string $fileName, string $oldDestination, string $newDestination): string;

    public function delete(string $fileName, string $destination): void;
}
