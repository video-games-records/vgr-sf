<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\Interface;

use App\SharedKernel\Domain\Event\DomainEvent;

interface EventDispatcherInterface
{
    public function dispatch(DomainEvent $event): void;
}
