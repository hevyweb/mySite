<?php

namespace App\Service\FileSystem;

use ZipArchive;

class ZipManager implements ZipManagerInterface
{
    #[\Override]
    public function zip(string $sourcePath, string $destinationZipPath): void
    {
        if (!file_exists($sourcePath)) {
            throw new \InvalidArgumentException(sprintf('Source path "%s" does not exist.', $sourcePath));
        }

        $zip = new ZipArchive();
        if (true !== $zip->open($destinationZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            throw new \RuntimeException(sprintf('Could not create zip archive at "%s".', $destinationZipPath));
        }

        $sourcePath = realpath($sourcePath);

        if (is_dir($sourcePath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourcePath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($sourcePath) + 1);

                if ($file->isDir()) {
                    $zip->addEmptyDir($relativePath);
                } elseif ($file->isFile()) {
                    $zip->addFile($filePath, $relativePath);
                }
            }
        } else {
            $zip->addFile($sourcePath, basename($sourcePath));
        }

        if (!$zip->close()) {
            throw new \RuntimeException(sprintf('Could not save zip archive at "%s".', $destinationZipPath));
        }
    }

    #[\Override]
    public function unzip(string $zipFilePath, string $destinationPath): void
    {
        if (!file_exists($zipFilePath)) {
            throw new \InvalidArgumentException(sprintf('Zip file "%s" does not exist.', $zipFilePath));
        }

        $zip = new ZipArchive();
        if (true !== $zip->open($zipFilePath)) {
            throw new \RuntimeException(sprintf('Could not open zip archive at "%s".', $zipFilePath));
        }

        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        // extractTo() recursively recreates directories and overwrites existing files by default
        if (!$zip->extractTo($destinationPath)) {
            $zip->close();
            throw new \RuntimeException(sprintf('Failed to extract zip archive to "%s".', $destinationPath));
        }

        $zip->close();
    }
}