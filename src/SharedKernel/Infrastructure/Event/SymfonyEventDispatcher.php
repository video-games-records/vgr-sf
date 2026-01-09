<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\Event;

use App\SharedKernel\Domain\Event\DomainEvent;
use App\SharedKernel\Domain\Interface\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

class SymfonyEventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private readonly SymfonyEventDispatcherInterface $eventDispatcher
    ) {
    }

    public function dispatch(DomainEvent $event): void
    {
        $this->eventDispatcher->dispatch($event);
    }
}
