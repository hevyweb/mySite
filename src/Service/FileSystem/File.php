<?php

namespace App\Service\FileSystem;

use App\Service\StringService;
use PHPUnit\Runner\DirectoryCannotBeCreatedException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class File
{
    public function __construct(private StringService $strings)
    {
    }

    public function saveFileTo(UploadedFile $file, string $dir): SymfonyFile
    {
        $this->checkAndCreateFolder($dir);
        $fileName = $this->generateUniqueFilename($file->getClientOriginalExtension(), $dir);

        return $file->move($dir, $fileName);
    }

    public function generateUniqueFilename(string $ext, string $dir): string
    {
        do {
            $fileName = $this->strings->generateRandomSlug().'.'.$ext;
        } while (file_exists($this->getFilePath($dir, $fileName)));

        return $fileName;
    }

    public function remove(string $fileName, string $dir): void
    {
        $filePath = $this->getFilePath($dir, $fileName);

        if (is_file($filePath) && !unlink($filePath)) {
            throw new FileNotFoundException('Can not delete file "'.$filePath.'"');
        }
    }

    public function getFilePath(string $dir, string $fileName): string
    {
        return rtrim($dir, '/').DIRECTORY_SEPARATOR.$fileName;
    }

    public function checkAndCreateFolder(string $dir): void
    {
        if (!is_dir($dir) && !mkdir($dir)) {
            throw new DirectoryCannotBeCreatedException('Directory "'.$dir.'" can not be created.');
        }
    }

    public function copy(string $filePath, string $dir): string
    {
        $ext = pathinfo($filePath, \PATHINFO_EXTENSION);
        $newFileName = $this->generateUniqueFilename($ext, $dir);
        $newPath = $this->getFilePath($dir, $newFileName);

        if (!@copy($filePath, $newPath)) {
            throw new FileException('Can not copy file from "'.$filePath.'" to "'.$newPath.'"');
        }

        return $newFileName;
    }
}
