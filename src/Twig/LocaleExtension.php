<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class LocaleExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('locale', [$this, 'convertLocaleToUi']),
        ];
    }

    public function convertLocaleToUi(string $value): string
    {
        switch ($value) {
            case 'ua':
                return 'uk';
            default:
                return $value;
        }
    }
}
