<?php

namespace App\Service\FileSystem;

interface ZipManagerInterface
{
    /**
     * Creates a zip archive from a source file or directory.
     */
    public function zip(string $sourcePath, string $destinationZipPath): void;

    /**
     * Extracts a zip archive to a destination path, overwriting existing files and folders recursively.
     */
    public function unzip(string $zipFilePath, string $destinationPath): void;
}