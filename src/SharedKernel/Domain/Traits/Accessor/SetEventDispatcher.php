<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\Traits\Accessor;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

trait SetEventDispatcher
{
    private readonly EventDispatcherInterface $eventDispatcher;

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }
}
