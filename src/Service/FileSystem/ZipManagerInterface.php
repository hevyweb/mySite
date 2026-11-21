<?php

namespace App\Service\FileSystem;

interface ZipManagerInterface
{
    /**
     * Creates a zip archive from a source file or directory.
     *
     * @psalm-suppress PossiblyUnusedMethod
     *
     * @TODO this method is not used yet. Perhaps I will use it later.
     */
    public function zip(string $sourcePath, string $destinationZipPath): void;

    /**
     * Extracts a zip archive to a destination path, overwriting existing files and folders recursively.
     */
    public function unzip(string $zipFilePath, string $destinationPath): void;
}
