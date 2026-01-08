<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\Traits\Accessor;

use Symfony\Component\HttpFoundation\RequestStack;

trait SetRequestStack
{
    private RequestStack $requestStack;

    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    public function getRequestStack(): RequestStack
    {
        return $this->requestStack;
    }
}
