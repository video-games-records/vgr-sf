<?php

declare(strict_types=1);

namespace App\BoundedContext\Message\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\Message\Domain\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Message::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Message::class)]
class MessageListener
{
    public function __construct(
        #[Autowire(service: 'html_sanitizer.sanitizer.app.content_sanitizer')]
        private readonly HtmlSanitizerInterface $sanitizer,
    ) {
    }

    public function prePersist(Message $message): void
    {
        $this->purifyMessage($message);
    }

    public function preUpdate(Message $message): void
    {
        $this->purifyMessage($message);
    }

    private function purifyMessage(Message $message): void
    {
        if ($message->getMessage()) {
            $message->setMessage($this->sanitizer->sanitize($message->getMessage()));
        }
    }
}
