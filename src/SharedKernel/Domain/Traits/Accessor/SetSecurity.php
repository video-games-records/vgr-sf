<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\Traits\Accessor;

use Symfony\Bundle\SecurityBundle\Security;

trait SetSecurity
{
    private Security $security;

    public function setSecurity(Security $security): static
    {
        $this->security = $security;
        return $this;
    }

    public function getSecurity(): Security
    {
        return $this->security;
    }
}
