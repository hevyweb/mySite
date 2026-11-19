<?php

namespace App\Tests\Mock;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class FilesystemMock extends Filesystem
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
        
    }
    public function copy(string $originFile, string $targetFile, bool $overwriteNewerFiles = false): void
    {
        $this->logger->debug('Copying file '.$originFile.' to '.$targetFile);
    }

    public function mkdir(string|iterable $dirs, int $mode = 0777): void
    {
        $this->logger->debug('Creating directory '.$dirs);
    }

    public function exists(string|iterable $files): bool
    {
        $this->logger->debug('Checking existence of '.$files);

        return true;
    }

    public function touch(string|iterable $files, ?int $time = null, ?int $atime = null): void
    {
        $this->logger->debug('Touching '.$files);
    }
    
    public function remove(string|iterable $files): void
    {
        $this->logger->debug('Removing '.$files);
    }

    public function chmod(string|iterable $files, int $mode, int $umask = 0000, bool $recursive = false): void
    {
        $this->logger->debug('Changing mode '.$files);
    }

    public function chown(string|iterable $files, string|int $user, bool $recursive = false): void
    {
        $this->logger->debug('Changing owner '.$files);
    }

    public function chgrp(string|iterable $files, string|int $group, bool $recursive = false): void
    {
        $this->logger->debug('Changing group '.$files);
    }

    public function rename(string $origin, string $target, bool $overwrite = false): void
    {
        $this->logger->debug('Renaming '.$origin.' to '.$target);
    }

    public function symlink(string $originDir, string $targetDir, bool $copyOnWindows = false): void
    {
        $this->logger->debug('Creating symlink '.$originDir.' to '.$targetDir);
    }

    public function hardlink(string $originFile, string|iterable $targetFiles): void
    {
        $this->logger->debug('Creating hardlink '.$originFile.' to '.$targetFiles);
    }

    public function readlink(string $path, bool $canonicalize = false): ?string
    {
        $this->logger->debug('Reading symlink '.$path);
        return readlink($path);
    }

    public function makePathRelative(string $endPath, string $startPath): string
    {
        $this->logger->debug('Making path relative '.$endPath.' from '.$startPath);
        return $endPath;
    }

    public function mirror(string $originDir, string $targetDir, ?\Traversable $iterator = null, array $options = []): void
    {
        $this->logger->debug('Mirroring '.$originDir.' to '.$targetDir);
    }

    public function isAbsolutePath(string $file): bool
    {
        $this->logger->debug('Checking if '.$file.' is an absolute path');
        return true;
    }

    public function tempnam(string $dir, string $prefix, string $suffix = ''): string
    {
        $this->logger->debug('Creating temporary file in '.$dir.' with prefix '.$prefix.' and suffix '.$suffix);
        return '';
    }

    public function dumpFile(string $filename, $content): void
    {
        $this->logger->debug('Dumping file '.$filename);
    }

    public function appendToFile(string $filename, $content, bool $lock = false): void
    {
        $this->logger->debug('Appending to file '.$filename);
    }

    public function readFile(string $filename): string
    {
        $this->logger->debug('Reading file '.$filename);
        return '';
    }

    /**
     * @internal
     */
    public static function handleError(int $type, string $msg): void
    {
    }
}