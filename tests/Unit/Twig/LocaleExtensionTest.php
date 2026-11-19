<?php

namespace App\Tests\Unit\Twig;

use App\Twig\LocaleExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\TwigFilter;

class LocaleExtensionTest extends TestCase
{
    private LocaleExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new LocaleExtension();
    }

    public function testGetFilters(): void
    {
        $filters = $this->extension->getFilters();
        $this->assertCount(1, $filters);
        $this->assertInstanceOf(TwigFilter::class, $filters[0]);
        $this->assertEquals('locale', $filters[0]->getName());
    }

    #[DataProvider('localeDataProvider')]
    public function testConvertLocaleToUi(string $input, string $expected): void
    {
        $this->assertSame($expected, $this->extension->convertLocaleToUi($input));
    }

    public static function localeDataProvider(): \Generator
    {
        yield 'ua converts to uk' => ['ua', 'uk'];
        yield 'uk remains uk' => ['uk', 'uk'];
        yield 'en remains en' => ['en', 'en'];
        yield 'pl remains pl' => ['pl', 'pl'];
        yield 'unknown locale remains unchanged' => ['fr', 'fr'];
    }
}
