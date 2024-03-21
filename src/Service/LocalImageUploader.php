<?php

namespace App\Service;

use App\Service\File as FileService;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\FileBag;

readonly class LocalImageUploader implements ImageUploader
{
    public function __construct(
        private FileService $fileService,
        private ImageService $imageService,
    ) {
    }

    /**
     * @throws \ImagickException
     */
    public function save(FileBag $fileBag, string $destination): File
    {
        if ($fileBag->count()) {
            $file = $this->fileService->saveFileTo($fileBag->getIterator()->current(), $destination);
            $this->imageService->sanitize($file);

            return $file;
        } else {
            throw new UploadException('File is not uploaded.');
        }
    }

    public function delete(string $fileName, string $destination): bool
    {
        return $this->fileService->remove($fileName, $destination);
    }
}
