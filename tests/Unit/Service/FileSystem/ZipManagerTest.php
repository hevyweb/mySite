<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\FileSystem;

use App\Service\FileSystem\ZipManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class ZipManagerTest extends TestCase
{
    private ZipManager $zipManager;
    private Filesystem $filesystem;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->zipManager = new ZipManager();
        $this->filesystem = new Filesystem();
        $this->tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'zip_manager_test_'.uniqid('', true);
        $this->filesystem->mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        if ($this->filesystem->exists($this->tempDir)) {
            $this->makeDirectoryTreeWritable($this->tempDir);
            $this->filesystem->remove($this->tempDir);
        }
    }

    public function testZipSingleFileCreatesArchiveWithBasename(): void
    {
        $sourceFile = $this->tempDir.'/hello.txt';
        $zipPath = $this->tempDir.'/archive.zip';
        file_put_contents($sourceFile, 'Hello world');

        $this->zipManager->zip($sourceFile, $zipPath);

        $this->assertFileExists($zipPath);

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($zipPath));
        $this->assertSame(1, $zip->numFiles);
        $this->assertSame('Hello world', $zip->getFromName('hello.txt'));
        $zip->close();
    }

    public function testZipDirectoryRecursivelyIncludesFilesAndSubdirectories(): void
    {
        $sourceDir = $this->tempDir.'/project';
        $zipPath = $this->tempDir.'/project.zip';
        $this->filesystem->mkdir($sourceDir.'/sub/nested');
        file_put_contents($sourceDir.'/readme.md', '# Project');
        file_put_contents($sourceDir.'/sub/app.php', '<?php echo 1;');
        file_put_contents($sourceDir.'/sub/nested/config.json', '{"env":"test"}');

        $this->zipManager->zip($sourceDir, $zipPath);

        $this->assertFileExists($zipPath);

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($zipPath));
        $this->assertSame('# Project', $zip->getFromName('readme.md'));
        $this->assertSame('<?php echo 1;', $zip->getFromName('sub/app.php'));
        $this->assertSame('{"env":"test"}', $zip->getFromName('sub/nested/config.json'));
        $zip->close();
    }

    public function testZipThrowsInvalidArgumentExceptionWhenSourceDoesNotExist(): void
    {
        $missingPath = $this->tempDir.'/does-not-exist.txt';
        $zipPath = $this->tempDir.'/out.zip';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Source path "%s" does not exist.', $missingPath));

        $this->zipManager->zip($missingPath, $zipPath);
    }

    public function testUnzipCreatesDestinationDirectoryAndExtractsFiles(): void
    {
        $zipPath = $this->tempDir.'/bundle.zip';
        $extractDir = $this->tempDir.'/extracted/nested';

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($zipPath, \ZipArchive::CREATE));
        $zip->addFromString('index.txt', 'Extracted content');
        $zip->addFromString('folder/data.json', '{"ok":true}');
        $zip->close();

        $this->zipManager->unzip($zipPath, $extractDir);

        $this->assertDirectoryExists($extractDir);
        $this->assertSame('Extracted content', file_get_contents($extractDir.'/index.txt'));
        $this->assertSame('{"ok":true}', file_get_contents($extractDir.'/folder/data.json'));
    }

    public function testUnzipThrowsInvalidArgumentExceptionWhenZipDoesNotExist(): void
    {
        $missingZip = $this->tempDir.'/missing.zip';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Zip file "%s" does not exist.', $missingZip));

        $this->zipManager->unzip($missingZip, $this->tempDir.'/out');
    }

    public function testUnzipThrowsRuntimeExceptionWhenFileIsNotAValidZip(): void
    {
        $corruptZip = $this->tempDir.'/corrupt.zip';
        file_put_contents($corruptZip, 'not a valid zip archive');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Could not open zip archive at "%s".', $corruptZip));

        $this->zipManager->unzip($corruptZip, $this->tempDir.'/out');
    }

    private function makeDirectoryTreeWritable(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        chmod($path, 0777);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                chmod($item->getPathname(), 0777);
            }
        }
    }
}
