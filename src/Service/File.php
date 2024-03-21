<?php

namespace App\Service;

use PHPUnit\Runner\DirectoryCannotBeCreatedException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class File
{
    public function __construct(private Strings $strings)
    {
    }

    public function saveFileTo(UploadedFile $file, string $dir): SymfonyFile
    {
        $this->checkAndCreateFolder($dir);
        $fileName = $this->generateUniqueFilename($file->getClientOriginalExtension(), $dir);

        return $file->move($dir, $fileName);
    }

    public function generateUniqueFilename(string $ext, $dir): string
    {
        do {
            $fileName = $this->strings->generateRandomSlug().'.'.$ext;
        } while (file_exists($this->getFilePath($dir, $fileName)));

        return $fileName;
    }

    public function remove($fileName, $dir): bool
    {
        $filePath = $this->getFilePath($dir, $fileName);

        if (is_file($filePath) && !unlink($filePath)) {
            throw new FileNotFoundException('Can not delete file "'.$filePath.'"');
        }

        return true;
    }

    public function getFilePath(string $dir, string $fileName): string
    {
        return rtrim($dir, '/').DIRECTORY_SEPARATOR.$fileName;
    }

    public function checkAndCreateFolder($dir): void
    {
        if (!is_dir($dir) && !mkdir($dir)) {
            throw new DirectoryCannotBeCreatedException('Directory "'.$dir.'" can not be created.');
        }
    }
}
