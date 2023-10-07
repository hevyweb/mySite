<?php

namespace App\Traits;

trait LocaleBuilderTrait
{
    private array $locales;

    /**
     * @param array $locales
     * @return void
     * @required
     */
    public function setLocales(array $locales)
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