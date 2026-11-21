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
        $this->logger->debug('Copying file');
    }

    /**
     * @param string|iterable<mixed, string> $dirs
     */
    public function mkdir(string|iterable $dirs, int $mode = 0777): void
    {
        $this->logger->debug('Creating directory');
    }

    /**
     * @param string|iterable<mixed, string> $files
     */
    public function exists(string|iterable $files): bool
    {
        $this->logger->debug('Checking existence');

        return false;
    }

    /**
     * @param string|iterable<mixed, string> $files
     */
    public function touch(string|iterable $files, ?int $time = null, ?int $atime = null): void
    {
        $this->logger->debug('Touching');
    }

    /**
     * @param string|iterable<mixed, string> $files
     */
    public function remove(string|iterable $files): void
    {
        $this->logger->debug('Removing');
    }

    /**
     * @param string|iterable<mixed, string> $files
     */
    public function chmod(string|iterable $files, int $mode, int $umask = 0000, bool $recursive = false): void
    {
        $this->logger->debug('Changing mode');
    }

    /**
     * @param string|iterable<mixed, string> $files
     */
    public function chown(string|iterable $files, string|int $user, bool $recursive = false): void
    {
        $this->logger->debug('Changing owner');
    }

    /**
     * @param string|iterable<mixed, string> $files
     */
    public function chgrp(string|iterable $files, string|int $group, bool $recursive = false): void
    {
        $this->logger->debug('Changing group');
    }

    public function rename(string $origin, string $target, bool $overwrite = false): void
    {
        $this->logger->debug('Renaming');
    }

    public function symlink(string $originDir, string $targetDir, bool $copyOnWindows = false): void
    {
        $this->logger->debug('Creating symlink');
    }

    /**
     * @param string|iterable<mixed, string> $targetFiles
     */
    public function hardlink(string $originFile, string|iterable $targetFiles): void
    {
        $this->logger->debug('Creating hardlink');
    }

    public function readlink(string $path, bool $canonicalize = false): ?string
    {
        $this->logger->debug('Reading symlink');

        return readlink($path);
    }

    public function makePathRelative(string $endPath, string $startPath): string
    {
        $this->logger->debug('Making path relative');

        return $endPath;
    }

    /**
     * @param ?\Traversable<mixed, mixed> $iterator
     * @param array<string, mixed>        $options
     */
    public function mirror(string $originDir, string $targetDir, ?\Traversable $iterator = null, array $options = []): void
    {
        $this->logger->debug('Mirroring');
    }

    public function isAbsolutePath(string $file): bool
    {
        $this->logger->debug('Checking if is an absolute path');

        return true;
    }

    public function tempnam(string $dir, string $prefix, string $suffix = ''): string
    {
        $this->logger->debug('Creating temporary file in with prefix and suffix');

        return '';
    }

    public function dumpFile(string $filename, $content): void
    {
        $this->logger->debug('Dumping file');
    }

    public function appendToFile(string $filename, $content, bool $lock = false): void
    {
        $this->logger->debug('Appending to file');
    }

    public function readFile(string $filename): string
    {
        $this->logger->debug('Reading file');

        return '';
    }

    /**
     * @internal
     */
    public static function handleError(int $type, string $msg): void
    {
    }
}
