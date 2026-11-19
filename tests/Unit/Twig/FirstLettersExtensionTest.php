<?php

namespace App\Tests\Unit\Twig;

use App\Twig\FirstLettersExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\TwigFilter;

class FirstLettersExtensionTest extends TestCase
{
    private FirstLettersExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new FirstLettersExtension();
    }

    public function testGetFilters(): void
    {
        $filters = $this->extension->getFilters();
        $this->assertCount(1, $filters);
        $this->assertInstanceOf(TwigFilter::class, $filters[0]);
        $this->assertEquals('firstLetters', $filters[0]->getName());
    }

    #[DataProvider('firstLettersDataProvider')]
    public function testGetFirstLetters(string $input, string $expected): void
    {
        $this->assertSame($expected, $this->extension->getFirstLetters($input));
    }

    public static function firstLettersDataProvider(): \Generator
    {
        yield 'two words' => ['John Doe', 'JD'];
        yield 'single word' => ['John', 'J'];
        yield 'multiple spaces' => ['John   Doe', 'JD'];
        yield 'lowercase input' => ['john doe', 'JD'];
        yield 'three words stops at two' => ['John Fitzgerald Kennedy', 'JF'];
        yield 'cyrillic characters' => ['Дмитро Дзюба', 'ДД'];
        yield 'empty string' => ['', ''];
        yield 'whitespace only' => ['   ', ''];
    }
}
