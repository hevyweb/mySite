<?php

namespace App\Tests\Unit\Service\FileSystem;

use App\Exception\DirectoryCannotBeCreatedException;
use App\Service\FileSystem\File;
use App\Service\StringService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileTest extends TestCase
{
    private StringService $stringService;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->stringService = $this->createMock(StringService::class);
        $this->tempDir = sys_get_temp_dir().'/file_test_'.uniqid();
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir.DIRECTORY_SEPARATOR.$file;
            is_dir($path) ? $this->removeDirectory($path) : @unlink($path);
        }
        @rmdir($dir);
    }

    public function testGetFilePath(): void
    {
        $fileService = new File($this->stringService, 'test');
        $path = $fileService->getFilePath('/path/to/dir/', 'file.txt');

        $this->assertSame('/path/to/dir'.DIRECTORY_SEPARATOR.'file.txt', $path);
    }

    public function testGenerateUniqueFilenameAvoidsExistingFiles(): void
    {
        mkdir($this->tempDir, 0777, true);
        touch($this->tempDir.DIRECTORY_SEPARATOR.'existing-slug.jpg');

        $this->stringService->expects($this->exactly(2))
            ->method('generateRandomSlug')
            ->willReturnOnConsecutiveCalls('existing-slug', 'unique-slug');

        $fileService = new File($this->stringService, 'test');
        $filename = $fileService->generateUniqueFilename('jpg', $this->tempDir);

        $this->assertSame('unique-slug.jpg', $filename);
    }

    public function testCheckAndCreateFolderCreatesDirectory(): void
    {
        $fileService = new File($this->stringService, 'test');
        $this->assertDirectoryDoesNotExist($this->tempDir);

        $fileService->checkAndCreateFolder($this->tempDir);

        $this->assertDirectoryExists($this->tempDir);
    }

    public function testCheckAndCreateFolderThrowsExceptionWhenDirectoryCannotBeCreated(): void
    {
        // Create a file where we want the directory to be, making mkdir fail
        mkdir($this->tempDir, 0777, true);
        $blockingFile = $this->tempDir.DIRECTORY_SEPARATOR.'blocker';
        touch($blockingFile);

        $fileService = new File($this->stringService, 'test');

        $this->expectException(DirectoryCannotBeCreatedException::class);
        $fileService->checkAndCreateFolder($blockingFile);
    }

    public function testSaveFileToInTestEnvironment(): void
    {
        $this->stringService->expects($this->once())
            ->method('generateRandomSlug')
            ->willReturn('test-file');

        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->expects($this->once())
            ->method('getClientOriginalExtension')
            ->willReturn('png');

        $fileService = new File($this->stringService, 'test');
        $result = $fileService->saveFileTo($uploadedFile, $this->tempDir);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\File\File::class, $result);
        $this->assertStringContainsString('test-file.png', $result->getPathname());
    }

    public function testSaveFileToInProdEnvironment(): void
    {
        $this->stringService->expects($this->once())
            ->method('generateRandomSlug')
            ->willReturn('prod-file');

        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->expects($this->once())
            ->method('getClientOriginalExtension')
            ->willReturn('jpg');

        $movedFileMock = $this->createMock(\Symfony\Component\HttpFoundation\File\File::class);
        $uploadedFile->expects($this->once())
            ->method('move')
            ->with($this->tempDir, 'prod-file.jpg')
            ->willReturn($movedFileMock);

        $fileService = new File($this->stringService, 'prod');
        $result = $fileService->saveFileTo($uploadedFile, $this->tempDir);

        $this->assertSame($movedFileMock, $result);
    }

    public function testSaveFileContentsInProdEnvironment(): void
    {
        $fileService = new File($this->stringService, 'prod');
        $fileName = 'contents.txt';

        $fileService->saveFileContents('hello world', $this->tempDir, $fileName);

        $filePath = $this->tempDir.DIRECTORY_SEPARATOR.$fileName;
        $this->assertFileExists($filePath);
        $this->assertSame('hello world', file_get_contents($filePath));
    }

    public function testCopyInProdEnvironmentSuccessfullyCopies(): void
    {
        mkdir($this->tempDir, 0777, true);
        $sourceFile = $this->tempDir.DIRECTORY_SEPARATOR.'source.txt';
        file_put_contents($sourceFile, 'test data');

        $targetDir = $this->tempDir.DIRECTORY_SEPARATOR.'target';
        mkdir($targetDir, 0777, true); // Ensure target directory exists for copy()

        $this->stringService->expects($this->once())
            ->method('generateRandomSlug')
            ->willReturn('copied');

        $fileService = new File($this->stringService, 'prod');
        $newFilename = $fileService->copy($sourceFile, $targetDir);

        $this->assertSame('copied.txt', $newFilename);
        $this->assertFileExists($targetDir.DIRECTORY_SEPARATOR.'copied.txt');
        $this->assertSame('test data', file_get_contents($targetDir.DIRECTORY_SEPARATOR.'copied.txt'));
    }

    public function testCopyThrowsFileExceptionOnFailure(): void
    {
        $nonExistentSource = $this->tempDir.DIRECTORY_SEPARATOR.'non_existent.txt';
        $targetDir = $this->tempDir.DIRECTORY_SEPARATOR.'target';

        $this->stringService->expects($this->once())
            ->method('generateRandomSlug')
            ->willReturn('copied');

        $fileService = new File($this->stringService, 'prod');

        $this->expectException(FileException::class);
        $fileService->copy($nonExistentSource, $targetDir);
    }

    public function testRemoveInProdEnvironmentSuccessfullyDeletesFile(): void
    {
        mkdir($this->tempDir, 0777, true);
        $fileName = 'to_delete.txt';
        $filePath = $this->tempDir.DIRECTORY_SEPARATOR.$fileName;
        file_put_contents($filePath, 'delete me');

        $fileService = new File($this->stringService, 'prod');
        $fileService->remove($fileName, $this->tempDir);

        $this->assertFileDoesNotExist($filePath);
    }
}
