<?php

namespace App\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

readonly class Language
{
    public function __construct(
        private TranslatorInterface $translator,
        private array $locales
    ) {
    }

    public function buildLanguages(): array
    {
        $languages = [];
        foreach ($this->locales as $locale) {
            $languages[$this->translator->trans($locale, [], 'languages')] = $locale;
        }

        return $languages;
    }
}
