<?php

namespace App\Service\FileSystem;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class FileManager implements FileManagerInterface
{
    public function __construct(
        private Filesystem $filesystem,
    ) {
        echo get_class($this->filesystem);
    }

    #[\Override]
    public function save(UploadedFile $file, string $destination): string
    {
        if ($file->isFile()) {
            $this->checkAndCreateFolder($destination);
            $fileName = $this->generateUniqueFilename($file->getClientOriginalExtension(), $destination);
            $this->filesystem->copy($file->getPathname(), $destination.DIRECTORY_SEPARATOR.$fileName);

            return $fileName;
        } else {
            throw new UploadException('File is not uploaded.');
        }
    }

    #[\Override]
    public function remove(string $fileName, ?string $destination = null): void
    {
        if (null !== $destination) {
            $fileName = $destination.DIRECTORY_SEPARATOR.$fileName;
        }
        $this->filesystem->remove($fileName);
    }

    #[\Override]
    public function copy(string $fileName, string $oldDestination, string $newDestination): string
    {
        $oldPath = $oldDestination.DIRECTORY_SEPARATOR.$fileName;
        $ext = pathinfo($oldPath, \PATHINFO_EXTENSION);

        $newPath = $newDestination.DIRECTORY_SEPARATOR.$this->generateUniqueFilename($ext, $newDestination);

        $this->filesystem->copy($oldPath, $newPath);
        return $newPath;
    }
    
    public function checkAndCreateFolder(string $dir): void
    {
        if (!$this->filesystem->exists($dir)) {
            $this->filesystem->mkdir($dir);
        }
    }
    
    public function generateUniqueFilename(string $extension, string $dir): string
    {
        do {
            $filename = uniqid() . '.' . $extension;
        } while ($this->filesystem->exists($dir . DIRECTORY_SEPARATOR . $filename));
        
        return $filename;
    }

    public function saveFileContents(string $contents, string $destination, string $fileName): void
    {
        $this->filesystem->dumpFile($destination.DIRECTORY_SEPARATOR.$fileName, $contents);
    }
}
