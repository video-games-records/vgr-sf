<?php

namespace App\SharedKernel\Domain\Event;

use Symfony\Contracts\EventDispatcher\Event;

abstract class DomainEvent extends Event
{
    private readonly \DateTime $occurredOn;

    public function __construct()
    {
        $this->occurredOn = new \DateTime();
    }

    public function getOccurredOn(): \DateTime
    {
        return $this->occurredOn;
    }
}
