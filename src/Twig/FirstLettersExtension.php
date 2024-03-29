<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FirstLettersExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('firstLetters', [$this, 'getFirstLetters']),
        ];
    }

    public function getFirstLetters($value): string
    {
        $words = explode(' ', $value);

        $words = array_filter($words);

        $return = '';

        $n = 0;

        foreach ($words as $word) {
            $return .= mb_strtoupper(mb_substr($word, 0, 1));
            ++$n;
            if (2 == $n) {
                break;
            }
        }

        return $return;
    }
}
