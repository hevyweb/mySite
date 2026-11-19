<?php

namespace App\Tests\Unit\Service;

use App\Service\ImageService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ImageServiceTest extends TestCase
{
    private string $tempImagePath;

    protected function setUp(): void
    {
        // Create a temporary 200x100 PNG image so Imagick can safely load and process it during tests
        $this->tempImagePath = sys_get_temp_dir().'/test_image_'.uniqid().'.png';
        $image = imagecreatetruecolor(200, 100);
        imagepng($image, $this->tempImagePath);
        imagedestroy($image);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempImagePath)) {
            unlink($this->tempImagePath);
        }
    }

    public function testThumbnailInTestEnvironmentReturnsPath(): void
    {
        $service = new ImageService('test');
        $result = $service->thumbnail($this->tempImagePath, 50, 50);

        $this->assertSame($this->tempImagePath, $result);
    }

    public function testThumbnailReturnsBlobWhenImageSmallerThanTarget(): void
    {
        $service = new ImageService('prod');
        // Image is 200x100, target is 300x300 (smaller in both dimensions)
        $result = $service->thumbnail($this->tempImagePath, 300, 300);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testThumbnailWithResizeAndCrop(): void
    {
        $service = new ImageService('prod');
        // Image is 200x100 (2:1 ratio), target is 50x50 (1:1 ratio)
        $result = $service->thumbnail($this->tempImagePath, 50, 50, true);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testThumbnailWithoutResizeFitWithinFrame(): void
    {
        $service = new ImageService('prod');
        $result = $service->thumbnail($this->tempImagePath, 50, 50, false);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    #[DataProvider('scaleDimensionsDataProvider')]
    public function testCalculateScaleDimensions(
        int $origWidth,
        int $origHeight,
        int $targetWidth,
        int $targetHeight,
        array $expected,
    ): void {
        $result = new ImageService('prod')->calculateScaleDimensions(
            $origWidth,
            $origHeight,
            $targetWidth,
            $targetHeight
        );

        $this->assertSame($expected, $result);
    }

    public static function scaleDimensionsDataProvider(): \Generator
    {
        yield 'wider original than target' => [
            200, 100, // 2:1 ratio
            50, 50,   // 1:1 target
            [100, 50], // Expected [scaleWidth, scaleHeight] matching target height
        ];

        yield 'taller original than target' => [
            100, 200, // 1:2 ratio
            50, 50,   // 1:1 target
            [50, 100], // Expected matching target width
        ];

        yield 'exact same aspect ratio' => [
            200, 200,
            100, 100,
            [100, 100],
        ];

        yield 'landscape to portrait constraint' => [
            1920, 1080,
            400, 600,
            [1067, 600],
        ];
    }
}
