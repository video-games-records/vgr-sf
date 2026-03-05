<?php

declare(strict_types=1);

namespace App\BoundedContext\Message\Domain\Entity;

use App\BoundedContext\Message\Domain\ValueObject\MessageTypeEnum;
use App\BoundedContext\Message\Infrastructure\Doctrine\Repository\MessageRepository;
use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Index(name: "idx_inbox", columns: ["recipient_id", 'is_deleted_recipient'])]
#[ORM\Index(name: "idx_outbox", columns: ["sender_id", 'is_deleted_sender'])]
#[ORM\Index(name: "idx_newMessage", columns: ["recipient_id", 'is_opened'])]
#[ORM\Index(name: "idx_inbox_type", columns: ["recipient_id", "is_deleted_recipient", "type"])]
#[ORM\Index(name: "idx_outbox_type", columns: ["sender_id", "is_deleted_sender", "type"])]
#[ORM\Index(name: "idx_inbox_opened", columns: ["recipient_id", "is_deleted_recipient", "is_opened"])]
#[ORM\Index(name: "idx_outbox_opened", columns: ["sender_id", "is_deleted_sender", "is_opened"])]
#[ORM\Table(name:'pnm_message')]
#[ORM\Entity(repositoryClass: MessageRepository::class)]

class Message
{
    use TimestampableEntity;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: false)]
    private string $object;

    #[ORM\Column(type:'text', nullable: true)]
    private ?string $message = null;

    #[Assert\Length(max: 50)]
    #[ORM\Column(length: 50, nullable: false, options: ['default' => 'DEFAULT'])]
    private string $type = 'DEFAULT';


    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name:'sender_id', referencedColumnName:'id', nullable:false)]
    private User $sender;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name:'recipient_id', referencedColumnName:'id', nullable:false)]
    private User $recipient;

    #[ORM\Column(nullable: false, options: ['default' => false])]
    private bool $isOpened = false;

    #[ORM\Column(nullable: false, options: ['default' => false])]
    private bool $isDeletedSender = false;

    #[ORM\Column(nullable: false, options: ['default' => false])]
    private bool $isDeletedRecipient = false;


    public function __toString()
    {
        return sprintf('Message [%s]', $this->id);
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setObject(string $object): void
    {
        $this->object = $object;
    }

    public function getObject(): string
    {
        return $this->object;
    }

    public function setType(string|MessageTypeEnum $type): void
    {
        $this->type = $type instanceof MessageTypeEnum ? $type->value : $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTypeEnum(): MessageTypeEnum
    {
        return MessageTypeEnum::tryFrom($this->type) ?? MessageTypeEnum::DEFAULT;
    }

    public function isReplyable(): bool
    {
        return $this->getTypeEnum()->isReplyable();
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(User $sender): void
    {
        $this->sender = $sender;
    }

    public function getRecipient(): User
    {
        return $this->recipient;
    }

    public function setRecipient(User $recipient): void
    {
        $this->recipient = $recipient;
    }

    public function setIsOpened(bool $isOpened): void
    {
        $this->isOpened = $isOpened;
    }

    public function getIsOpened(): bool
    {
        return $this->isOpened;
    }

    public function setIsDeletedSender(bool $isDeletedSender): void
    {
        $this->isDeletedSender = $isDeletedSender;
    }

    public function getIsDeletedSender(): bool
    {
        return $this->isDeletedSender;
    }

    public function setIsDeletedRecipient(bool $isDeletedRecipient): void
    {
        $this->isDeletedRecipient = $isDeletedRecipient;
    }

    public function getIsDeletedRecipient(): bool
    {
        return $this->isDeletedRecipient;
    }
}
