<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\Traits\Accessor;

use Symfony\Component\Messenger\MessageBusInterface;

trait SetMessageBus
{
    private MessageBusInterface $messageBus;

    public function setMessageBus(MessageBusInterface $messageBus): static
    {
        $this->messageBus = $messageBus;
        return $this;
    }

    public function getMessageBus(): MessageBusInterface
    {
        return $this->messageBus;
    }
}
