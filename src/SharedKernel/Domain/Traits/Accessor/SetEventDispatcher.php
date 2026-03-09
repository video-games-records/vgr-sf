<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\Traits\Accessor;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

trait SetEventDispatcher
{
    private EventDispatcherInterface $eventDispatcher;

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): static
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }
}
