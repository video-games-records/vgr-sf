<?php

declare(strict_types=1);

namespace App\BoundedContext\Message\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\Message\Domain\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use HTMLPurifier;
use HTMLPurifier_Config;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Message::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Message::class)]
class MessageListener
{
    private HTMLPurifier $purifier;

    public function __construct()
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,br,strong,em,u,ol,ul,li,a[href],h1,h2,h3,blockquote');
        $this->purifier = new HTMLPurifier($config);
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
            $message->setMessage($this->purifier->purify($message->getMessage()));
        }
    }
}
