<?php

namespace App\Tests\Unit\Service\FileSystem;

use App\Service\FileSystem\FileAntiVirus;
use PHPUnit\Framework\TestCase;

class FileAntiVirusTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/file_anti_virus_test_'.uniqid();
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    private function removeDirectory(string $dir): void
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir.DIRECTORY_SEPARATOR.$file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testSanitizeInTestEnvironmentReturnsEarly(): void
    {
        $antiVirus = new FileAntiVirus('test');

        // Should return early without checking file existence or interacting with Imagick
        $antiVirus->sanitize('non_existent.jpg', $this->tempDir);

        $this->assertTrue(true);
    }

    public function testSanitizeInProdEnvironmentProcessesImage(): void
    {
        if (!extension_loaded('imagick')) {
            $this->markTestSkipped('Imagick extension is not available.');
        }

        $fileName = 'test_image.png';
        $filePath = $this->tempDir.DIRECTORY_SEPARATOR.$fileName;

        $image = imagecreatetruecolor(10, 10);
        imagepng($image, $filePath);
        imagedestroy($image);

        $antiVirus = new FileAntiVirus('prod');
        $antiVirus->sanitize($fileName, $this->tempDir);

        $this->assertFileExists($filePath);
    }
}
