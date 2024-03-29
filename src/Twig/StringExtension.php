<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class StringExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('unserialize', 'unserialize'),
        ];
    }
}
