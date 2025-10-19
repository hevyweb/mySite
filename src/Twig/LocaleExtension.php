<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class LocaleExtension extends AbstractExtension
{
    #[\Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('locale', [$this, 'convertLocaleToUi']),
        ];
    }

    public function convertLocaleToUi(string $value): string
    {
        return match ($value) {
            'ua' => 'uk',
            default => $value,
        };
    }
}
