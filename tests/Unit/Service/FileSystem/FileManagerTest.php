<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\FileSystem;

use App\Service\FileSystem\FileManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class FileManagerTest extends TestCase
{
    private Filesystem&MockObject $filesystem;
    private FileManager $fileManager;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->fileManager = new FileManager($this->filesystem);
    }

    public function testSaveCopiesFileAndCreatesFolderWhenDirectoryDoesNotExist(): void
    {
        $destination = '/var/www/uploads';
        $sourcePath = '/tmp/php1234';

        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->expects($this->once())
            ->method('isFile')
            ->willReturn(true);
        $uploadedFile->expects($this->once())
            ->method('getClientOriginalExtension')
            ->willReturn('jpg');
        $uploadedFile->expects($this->once())
            ->method('getPathname')
            ->willReturn($sourcePath);

        $this->filesystem->expects($this->exactly(2))
            ->method('exists')
            ->willReturnCallback(static fn (string $path): bool => $path !== $destination && false);

        $this->filesystem->expects($this->once())
            ->method('mkdir')
            ->with($destination);

        $capturedTarget = null;
        $this->filesystem->expects($this->once())
            ->method('copy')
            ->with(
                $sourcePath,
                $this->callback(static function (string $target) use ($destination, &$capturedTarget): bool {
                    $capturedTarget = $target;

                    return (bool) preg_match(
                        '#^'.preg_quote($destination.DIRECTORY_SEPARATOR, '#').'[a-f0-9]+\.jpg$#',
                        $target
                    );
                })
            );

        $result = $this->fileManager->save($uploadedFile, $destination);

        self::assertMatchesRegularExpression('/^[a-f0-9]+\.jpg$/', $result);
        self::assertSame($destination.DIRECTORY_SEPARATOR.$result, $capturedTarget);
    }

    public function testSaveSkipsMkdirWhenDirectoryAlreadyExists(): void
    {
        $destination = '/var/www/uploads';
        $sourcePath = '/tmp/php1234';

        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('isFile')->willReturn(true);
        $uploadedFile->method('getClientOriginalExtension')->willReturn('png');
        $uploadedFile->method('getPathname')->willReturn($sourcePath);

        $this->filesystem->expects($this->exactly(2))
            ->method('exists')
            ->willReturnCallback(static fn (string $path): bool => $path === $destination);

        $this->filesystem->expects($this->never())
            ->method('mkdir');

        $this->filesystem->expects($this->once())
            ->method('copy');

        $result = $this->fileManager->save($uploadedFile, $destination);

        self::assertMatchesRegularExpression('/^[a-f0-9]+\.png$/', $result);
    }

    public function testSaveThrowsUploadExceptionWhenFileIsNotValidFile(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->expects($this->once())
            ->method('isFile')
            ->willReturn(false);

        $this->filesystem->expects($this->never())->method('copy');
        $this->filesystem->expects($this->never())->method('mkdir');

        $this->expectException(UploadException::class);
        $this->expectExceptionMessage('File is not uploaded.');

        $this->fileManager->save($uploadedFile, '/var/www/uploads');
    }

    public function testRemoveWithDestinationPrependsDirectory(): void
    {
        $this->filesystem->expects($this->once())
            ->method('remove')
            ->with('/var/www/uploads'.DIRECTORY_SEPARATOR.'image.jpg');

        $this->fileManager->remove('image.jpg', '/var/www/uploads');
    }

    public function testRemoveWithoutDestinationPassesFileNameDirectly(): void
    {
        $this->filesystem->expects($this->once())
            ->method('remove')
            ->with('/var/www/uploads/image.jpg');

        $this->fileManager->remove('/var/www/uploads/image.jpg');
    }

    public function testCopyGeneratesUniqueDestinationPathAndCopiesFile(): void
    {
        $oldDestination = '/var/www/uploads/old';
        $newDestination = '/var/www/uploads/new';
        $fileName = 'article.webp';

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $this->filesystem->expects($this->once())
            ->method('copy')
            ->with(
                $oldDestination.DIRECTORY_SEPARATOR.$fileName,
                $this->matchesRegularExpression(
                    '#^'.preg_quote($newDestination.DIRECTORY_SEPARATOR, '#').'[a-f0-9]+\.webp$#'
                )
            );

        $result = $this->fileManager->copy($fileName, $oldDestination, $newDestination);

        self::assertMatchesRegularExpression(
            '#^'.preg_quote($newDestination.DIRECTORY_SEPARATOR, '#').'[a-f0-9]+\.webp$#',
            $result
        );
    }

    public function testCheckAndCreateFolderCreatesDirectoryWhenMissing(): void
    {
        $dir = '/var/www/uploads/articles';

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with($dir)
            ->willReturn(false);

        $this->filesystem->expects($this->once())
            ->method('mkdir')
            ->with($dir);

        $this->fileManager->checkAndCreateFolder($dir);
    }

    public function testCheckAndCreateFolderDoesNothingWhenDirectoryExists(): void
    {
        $dir = '/var/www/uploads/articles';

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with($dir)
            ->willReturn(true);

        $this->filesystem->expects($this->never())
            ->method('mkdir');

        $this->fileManager->checkAndCreateFolder($dir);
    }

    public function testGenerateUniqueFilenameRetriesOnCollision(): void
    {
        $dir = '/var/www/uploads';
        $checkedPaths = [];

        $this->filesystem->expects($this->exactly(3))
            ->method('exists')
            ->willReturnCallback(static function (string $path) use (&$checkedPaths): bool {
                $checkedPaths[] = $path;

                return count($checkedPaths) < 3;
            });

        $filename = $this->fileManager->generateUniqueFilename('pdf', $dir);

        self::assertCount(3, $checkedPaths);
        self::assertSame($dir.DIRECTORY_SEPARATOR.$filename, $checkedPaths[2]);
        self::assertMatchesRegularExpression('/^[a-f0-9]+\.pdf$/', $filename);
    }

    public function testSaveFileContentsDumpsFileToDestination(): void
    {
        $this->filesystem->expects($this->once())
            ->method('dumpFile')
            ->with('/var/www/uploads'.DIRECTORY_SEPARATOR.'notes.txt', 'hello world');

        $this->fileManager->saveFileContents('hello world', '/var/www/uploads', 'notes.txt');
    }
}
