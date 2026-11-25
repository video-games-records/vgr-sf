<?php

declare(strict_types=1);

namespace App\SharedKernel\Application\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\BoundedContext\Message\Domain\ValueObject\MessageTypeEnum;
use App\BoundedContext\Message\Infrastructure\Builder\MessageBuilder;
use App\BoundedContext\User\Domain\Entity\User;
use App\SharedKernel\Domain\Entity\GroupMessageBroadcast;
use App\SharedKernel\Domain\ValueObject\GroupMessageBroadcastTypeEnum;

readonly class GroupMessageBroadcastSender
{
    public function __construct(
        private EntityManagerInterface $em,
        private MessageBuilder $messageBuilder,
    ) {
    }

    /**
     * @param User[] $recipients
     */
    public function send(
        GroupMessageBroadcastTypeEnum $groupType,
        int $groupId,
        User $sender,
        string $subject,
        string $message,
        array $recipients
    ): int {
        foreach ($recipients as $recipient) {
            $this->messageBuilder
                ->setType(MessageTypeEnum::ADMIN_NOTIF)
                ->setObject($subject)
                ->setMessage($message)
                ->setSender($sender)
                ->setRecipient($recipient)
                ->send();
        }

        $broadcast = new GroupMessageBroadcast();
        $broadcast->setGroupType($groupType);
        $broadcast->setGroupId($groupId);
        $broadcast->setSenderUserId((int) $sender->getId());
        $broadcast->setSubject($subject);
        $broadcast->setMessage($message);
        $broadcast->setRecipientCount(count($recipients));

        $this->em->persist($broadcast);
        $this->em->flush();

        return count($recipients);
    }
}
