<?php

namespace App\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

readonly class Language
{
    /**
     * @param array<string> $locales
     */
    public function __construct(
        private TranslatorInterface $translator,
        private array $locales
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function buildLanguages(): array
    {
        $languages = [];
        foreach ($this->locales as $locale) {
            $languages[$this->translator->trans($locale, [], 'languages')] = $locale;
        }

        return $languages;
    }
}
