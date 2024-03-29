<?php

namespace App\Traits;

use Symfony\Contracts\Service\Attribute\Required;

trait LocaleBuilderTrait
{
    private array $locales;

    #[Required]
    public function setLocales(array $locales): void
    {
        $this->locales = $locales;
    }

    private function buildLanguages(): array
    {
        $languages = [];
        foreach ($this->locales as $locale) {
            $languages[$this->translator->trans($locale, [], 'languages')] = $locale;
        }

        return $languages;
    }
}
