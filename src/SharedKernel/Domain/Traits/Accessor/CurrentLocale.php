<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\Traits\Accessor;

trait CurrentLocale
{
    private ?string $currentLocale = null;

    public function setCurrentLocale(string $locale): static
    {
        $this->currentLocale = $locale;
        return $this;
    }

    public function getCurrentLocale(): ?string
    {
        return $this->currentLocale;
    }
}
