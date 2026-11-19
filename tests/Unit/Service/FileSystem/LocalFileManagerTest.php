<?php

namespace App\Tests\Unit\Service\FileSystem;

use App\Service\FileSystem\File as FileService;
use App\Service\FileSystem\FileAntiVirusInterface;
use App\Service\FileSystem\LocalFileManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LocalFileManagerTest extends TestCase
{
    private FileService $fileService;
    private FileAntiVirusInterface $antiVirus;
    private LocalFileManager $fileManager;

    protected function setUp(): void
    {
        $this->fileService = $this->createMock(FileService::class);
        $this->antiVirus = $this->createMock(FileAntiVirusInterface::class);
        $this->fileManager = new LocalFileManager($this->fileService, $this->antiVirus);
    }

    public function testSaveSuccessfullySavesAndSanitizesFile(): void
    {
        $destination = '/uploads';
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->expects($this->once())
            ->method('isFile')
            ->willReturn(true);

        $savedFile = $this->createMock(SymfonyFile::class);
        $savedFile->expects($this->once())
            ->method('getFilename')
            ->willReturn('saved_file.jpg');

        $this->fileService->expects($this->once())
            ->method('saveFileTo')
            ->with($uploadedFile, $destination)
            ->willReturn($savedFile);

        $this->antiVirus->expects($this->once())
            ->method('sanitize')
            ->with('saved_file.jpg', $destination);

        $filename = $this->fileManager->save($uploadedFile, $destination);

        $this->assertSame('saved_file.jpg', $filename);
    }

    public function testSaveThrowsExceptionWhenFileIsNotUploaded(): void
    {
        $destination = '/uploads';
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->expects($this->once())
            ->method('isFile')
            ->willReturn(false);

        $this->fileService->expects($this->never())->method('saveFileTo');
        $this->antiVirus->expects($this->never())->method('sanitize');

        $this->expectException(UploadException::class);
        $this->expectExceptionMessage('File is not uploaded.');

        $this->fileManager->save($uploadedFile, $destination);
    }

    public function testDeleteRemovesFile(): void
    {
        $fileName = 'file.jpg';
        $destination = '/uploads';

        $this->fileService->expects($this->once())
            ->method('remove')
            ->with($fileName, $destination);

        $this->fileManager->delete($fileName, $destination);
    }

    public function testCopyCopiesFile(): void
    {
        $fileName = 'file.jpg';
        $oldDestination = '/old/path';
        $newDestination = '/new/path';
        $expectedOldPath = $oldDestination.DIRECTORY_SEPARATOR.$fileName;

        $this->fileService->expects($this->once())
            ->method('copy')
            ->with($expectedOldPath, $newDestination)
            ->willReturn('copied_file.jpg');

        $result = $this->fileManager->copy($fileName, $oldDestination, $newDestination);

        $this->assertSame('copied_file.jpg', $result);
    }
}
