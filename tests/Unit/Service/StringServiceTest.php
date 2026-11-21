<?php

namespace App\Tests\Unit\Service;

use App\Service\StringService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StringServiceTest extends TestCase
{
    private StringService $stringService;

    protected function setUp(): void
    {
        $this->stringService = new StringService();
    }

    public function testGenerateRandomStringDefaultLength(): void
    {
        $result = $this->stringService->generateRandomString();
        $this->assertSame(12, strlen($result));
    }

    #[DataProvider('lengthDataProvider')]
    public function testGenerateRandomStringCustomLength(int $length): void
    {
        $result = $this->stringService->generateRandomString($length);
        $this->assertSame($length, strlen($result));

        if ($length > 0) {
            $charset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*-_+=?';
            for ($i = 0; $i < $length; ++$i) {
                $this->assertStringContainsString($result[$i], $charset);
            }
        }
    }

    public function testGenerateRandomSlugDefaultLength(): void
    {
        $result = $this->stringService->generateRandomSlug();
        $this->assertSame(12, strlen($result));
    }

    #[DataProvider('lengthDataProvider')]
    public function testGenerateRandomSlugCustomLength(int $length): void
    {
        $result = $this->stringService->generateRandomSlug($length);
        $this->assertSame($length, strlen($result));

        if ($length > 0) {
            $this->assertMatchesRegularExpression('/^[0123456789abcdefghijklmnopqrstuvwxyz\-]*$/', $result);
        }
    }

    public static function lengthDataProvider(): \Generator
    {
        yield 'length 0' => [0];
        yield 'length 1' => [1];
        yield 'length 8' => [8];
        yield 'length 24' => [24];
    }
}
