<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Domain\Entity;

use App\SharedKernel\Domain\Security\SecurityEventTypeEnum;
use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'pnu_security_event')]
#[ORM\Entity]
#[ORM\Index(columns: ["user_id", "event_type", "created_at"], name: "search_idx")]
class SecurityEvent
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(type: 'string', length: 50)]
    private string $eventType;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $eventData = null;

    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $userAgent = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    /**
     * Get the event type as a SecurityEventType object
     */
    public function getEventTypeObject(): SecurityEventTypeEnum
    {
        return SecurityEventTypeEnum::from($this->eventType);
    }

    public function setEventType(string $eventType): static
    {
        $this->eventType = $eventType;
        return $this;
    }

    public function setEventTypeFromEnum(SecurityEventTypeEnum $eventType): static
    {
        $this->eventType = $eventType->value;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getEventData(): ?array
    {
        return $this->eventData;
    }

    /**
     * @param array<string, mixed>|null $eventData
     */
    public function setEventData(?array $eventData): static
    {
        $this->eventData = $eventData;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%s#%s', $this->getUser()->getUserIdentifier(), $this->eventType);
    }
}
