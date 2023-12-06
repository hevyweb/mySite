<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\FileBag;

class LocalImageUploader implements ImageUploader
{
    public function __construct(private \App\Service\File $fileService)
    {
    }

    public function save(FileBag $fileBag, string $destination): File
    {
        if ($fileBag->count()) {
            return $this->fileService->saveFileTo($fileBag->getIterator()->current(), $destination);
        } else {
            throw new UploadException('File is not uploaded.');
        }
    }

    public function delete(string $fileName, string $destination): bool
    {
        return $this->fileService->remove($fileName, $destination);
    }
}