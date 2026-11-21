<?php

namespace App\Tests\Unit\Service;

use App\Service\Language;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class LanguageTest extends TestCase
{
    private TranslatorInterface&MockObject $translator;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
    }

    public function testBuildLanguages(): void
    {
        $locales = ['en', 'uk'];

        $this->translator->expects($this->exactly(2))
            ->method('trans')
            ->willReturnCallback(function (string $id, array $parameters, string $domain) {
                $this->assertEquals('languages', $domain);
                $this->assertSame([], $parameters);

                return match ($id) {
                    'en' => 'English',
                    'uk' => 'Ukrainian',
                    default => $id,
                };
            });

        $languageService = new Language($this->translator, $locales);
        $result = $languageService->buildLanguages();

        $expected = [
            'English' => 'en',
            'Ukrainian' => 'uk',
        ];

        $this->assertSame($expected, $result);
    }

    public function testBuildLanguagesWithEmptyLocales(): void
    {
        $this->translator->expects($this->never())
            ->method('trans');

        $languageService = new Language($this->translator, []);
        $result = $languageService->buildLanguages();

        $this->assertSame([], $result);
    }
}
