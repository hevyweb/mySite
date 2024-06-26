<?php

namespace App\Service\FileSystem;

use App\Service\FileSystem\File as FileService;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\FileBag;

readonly class LocalFileManager implements FileManagementInterface
{
    public function __construct(
        private FileService $fileService,
        private FileAntiVirusInterface $antiVirus,
    ) {
    }

    public function save(FileBag $fileBag, string $destination): string
    {
        if ($fileBag->count()) {
            $fileName = $this->fileService->saveFileTo($fileBag->getIterator()->current(), $destination)->getFilename();
            $this->antiVirus->sanitize($fileName, $destination);

            return $fileName;
        } else {
            throw new UploadException('File is not uploaded.');
        }
    }

    public function delete(string $fileName, string $destination): void
    {
        $this->fileService->remove($fileName, $destination);
    }

    public function copy(string $fileName, string $oldDestination, string $newDestination): string
    {
        $oldPath = $oldDestination.DIRECTORY_SEPARATOR.$fileName;

        return $this->fileService->copy($oldPath, $newDestination);
    }
}
