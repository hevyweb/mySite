<?php

namespace App\Service\FileSystem;

use App\Service\FileSystem\File as FileService;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class LocalFileManager implements FileManagementInterface
{
    public function __construct(
        private FileService $fileService,
        private FileAntiVirusInterface $antiVirus,
    ) {
    }

    #[\Override]
    public function save(UploadedFile $file, string $destination): string
    {
        if ($file->isFile()) {
            $fileName = $this->fileService->saveFileTo($file, $destination)->getFilename();
            $this->antiVirus->sanitize($fileName, $destination);

            return $fileName;
        } else {
            throw new UploadException('File is not uploaded.');
        }
    }

    #[\Override]
    public function delete(string $fileName, string $destination): void
    {
        $this->fileService->remove($fileName, $destination);
    }

    #[\Override]
    public function copy(string $fileName, string $oldDestination, string $newDestination): string
    {
        $oldPath = $oldDestination.DIRECTORY_SEPARATOR.$fileName;

        return $this->fileService->copy($oldPath, $newDestination);
    }
}
