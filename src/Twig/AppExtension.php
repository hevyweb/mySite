<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_env', [$this, 'getEnvironmentVariable']),
        ];
    }

    /**
     * Return the value of the requested environment variable.
     *
     * @param string $name
     * @return string
     */
    public function getEnvironmentVariable(string $name): string
    {
        return $_ENV[$name];
    }
}