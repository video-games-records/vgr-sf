<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\SharedKernel\Domain\ValueObject\GroupMessageBroadcastTypeEnum;
use App\SharedKernel\Infrastructure\Doctrine\Repository\GroupMessageBroadcastRepository;

#[ORM\Table(name: 'vgr_group_message_broadcast')]
#[ORM\Index(name: 'idx_group_message_broadcast_group', columns: ['group_type', 'group_id'])]
#[ORM\Entity(repositoryClass: GroupMessageBroadcastRepository::class)]
class GroupMessageBroadcast
{
    use TimestampableEntity;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\Length(max: 20)]
    #[ORM\Column(length: 20, nullable: false)]
    private string $groupType;

    #[ORM\Column(nullable: false)]
    private int $groupId;

    #[ORM\Column(nullable: false)]
    private int $senderUserId;

    #[ORM\Column(length: 255, nullable: false)]
    private string $subject;

    #[ORM\Column(type: 'text', nullable: false)]
    private string $message;

    #[ORM\Column(nullable: false)]
    private int $recipientCount;

    public function __toString(): string
    {
        return sprintf('GroupMessageBroadcast [%s]', $this->id ?? '');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroupType(): string
    {
        return $this->groupType;
    }

    public function getGroupTypeEnum(): ?GroupMessageBroadcastTypeEnum
    {
        return GroupMessageBroadcastTypeEnum::tryFrom($this->groupType);
    }

    public function setGroupType(string|GroupMessageBroadcastTypeEnum $groupType): static
    {
        $this->groupType = $groupType instanceof GroupMessageBroadcastTypeEnum ? $groupType->value : $groupType;
        return $this;
    }

    public function getGroupId(): int
    {
        return $this->groupId;
    }

    public function setGroupId(int $groupId): static
    {
        $this->groupId = $groupId;
        return $this;
    }

    public function getSenderUserId(): int
    {
        return $this->senderUserId;
    }

    public function setSenderUserId(int $senderUserId): static
    {
        $this->senderUserId = $senderUserId;
        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getRecipientCount(): int
    {
        return $this->recipientCount;
    }

    public function setRecipientCount(int $recipientCount): static
    {
        $this->recipientCount = $recipientCount;
        return $this;
    }
}
