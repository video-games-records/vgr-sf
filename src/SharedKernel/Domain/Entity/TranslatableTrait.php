<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\Entity;

use Doctrine\Common\Collections\Collection;

trait TranslatableTrait
{
    use CurrentLocaleTrait;

    private const string DEFAULT_LOCALE = 'en';

    abstract public function getTranslations(): Collection;

    public function translate(?string $locale = null, bool $fallbackToDefault = true): ?object
    {
        $locale = $locale ?: $this->getCurrentLocale() ?: self::DEFAULT_LOCALE;

        if ($this->getTranslations()->containsKey($locale)) {
            $translation = $this->getTranslations()->get($locale);
            if ($this->hasTranslationContent($translation)) {
                return $translation;
            }
        }

        if (
            $fallbackToDefault
            && $locale !== self::DEFAULT_LOCALE
            && $this->getTranslations()->containsKey(self::DEFAULT_LOCALE)
        ) {
            $translation = $this->getTranslations()->get(self::DEFAULT_LOCALE);
            if ($this->hasTranslationContent($translation)) {
                return $translation;
            }
        }

        foreach ($this->getTranslations() as $translation) {
            if ($this->hasTranslationContent($translation)) {
                return $translation;
            }
        }

        return $this->getTranslations()->first() ?: null;
    }

    public function hasTranslation(string $locale): bool
    {
        return $this->getTranslations()->containsKey($locale);
    }

    /**
     * @return array<string>
     */
    public function getAvailableLocales(): array
    {
        return $this->getTranslations()->getKeys();
    }

    public function addTranslation(object $translation): void
    {
        if (!$this->getTranslations()->contains($translation)) {
            $this->setTranslatableOnTranslation($translation);
            $this->getTranslations()->set($this->getTranslationLocale($translation), $translation);
        }
    }

    public function removeTranslation(object $translation): void
    {
        $this->getTranslations()->removeElement($translation);
    }

    abstract protected function hasTranslationContent(object $translation): bool;

    abstract protected function setTranslatableOnTranslation(object $translation): void;

    abstract protected function getTranslationLocale(object $translation): string;
}
