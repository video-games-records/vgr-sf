<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\TopicUserLastVisitRepository;
use App\BoundedContext\User\Domain\Entity\User;

#[ORM\Table(name:'pnf_topic_user_last_visit')]
#[ORM\Entity(repositoryClass: TopicUserLastVisitRepository::class)]
#[ORM\UniqueConstraint(name: "uniq_topic_user_visit", columns: ["user_id", "topic_id"])]
#[ORM\Index(name: "idx_topic_user_last_visit_user_date", columns: ["user_id", "last_visited_at"])]
#[ORM\Index(name: "idx_topic_user_last_visit_topic_date", columns: ["topic_id", "last_visited_at"])]
class TopicUserLastVisit
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name:'user_id', referencedColumnName:'id', nullable:false, onDelete:'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Topic::class, inversedBy: 'userLastVisits')]
    #[ORM\JoinColumn(name:'topic_id', referencedColumnName:'id', nullable:false, onDelete:'CASCADE')]
    private Topic $topic;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $lastVisitedAt;

    #[ORM\Column(nullable: false, options: ['default' => false])]
    private bool $isNotify = false;

    public function __construct()
    {
        $this->lastVisitedAt = new \DateTime();
    }

    public function __toString(): string
    {
        return sprintf(
            '%s - %s (%s)',
            $this->getUser(),
            $this->getTopic(),
            $this->getLastVisitedAt()->format('Y-m-d H:i:s')
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setTopic(Topic $topic): static
    {
        $this->topic = $topic;
        return $this;
    }

    public function getTopic(): Topic
    {
        return $this->topic;
    }

    public function setLastVisitedAt(\DateTime $lastVisitedAt): static
    {
        $this->lastVisitedAt = $lastVisitedAt;
        return $this;
    }

    public function getLastVisitedAt(): \DateTime
    {
        return $this->lastVisitedAt;
    }

    public function setIsNotify(bool $isNotify): static
    {
        $this->isNotify = $isNotify;
        return $this;
    }

    public function getIsNotify(): bool
    {
        return $this->isNotify;
    }

    public function updateLastVisit(): void
    {
        $this->lastVisitedAt = new \DateTime();
    }

    public function wasVisitedAfter(\DateTime $date): bool
    {
        return $this->lastVisitedAt > $date;
    }

    public function isTopicRead(): bool
    {
        $lastMessage = $this->topic->getLastMessage();

        if (!$lastMessage) {
            return true;
        }

        return $this->lastVisitedAt >= $lastMessage->getCreatedAt();
    }
}
