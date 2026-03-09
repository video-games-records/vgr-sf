<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\Entity;

trait CurrentLocaleTrait
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
