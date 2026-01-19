<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\ForumUserLastVisitRepository;
use App\BoundedContext\User\Domain\Entity\User;

#[ORM\Table(name:'pnf_forum_user_last_visit')]
#[ORM\Entity(repositoryClass: ForumUserLastVisitRepository::class)]
#[ORM\UniqueConstraint(name: "uniq_forum_user_visit", columns: ["user_id", "forum_id"])]
#[ORM\Index(name: "idx_forum_user_last_visit_user_date", columns: ["user_id", "last_visited_at"])]
#[ORM\Index(name: "idx_forum_user_last_visit_forum_date", columns: ["forum_id", "last_visited_at"])]
class ForumUserLastVisit
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name:'user_id', referencedColumnName:'id', nullable:false, onDelete:'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Forum::class, inversedBy: 'userLastVisits')]
    #[ORM\JoinColumn(name:'forum_id', referencedColumnName:'id', nullable:false, onDelete:'CASCADE')]
    private Forum $forum;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $lastVisitedAt;

    public function __construct()
    {
        $this->lastVisitedAt = new \DateTime();
    }

    public function __toString(): string
    {
        return sprintf(
            '%s - %s (%s)',
            $this->getUser(),
            $this->getForum(),
            $this->getLastVisitedAt()->format('Y-m-d H:i:s')
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setForum(Forum $forum): void
    {
        $this->forum = $forum;
    }

    public function getForum(): Forum
    {
        return $this->forum;
    }

    public function setLastVisitedAt(\DateTime $lastVisitedAt): void
    {
        $this->lastVisitedAt = $lastVisitedAt;
    }

    public function getLastVisitedAt(): \DateTime
    {
        return $this->lastVisitedAt;
    }

    public function updateLastVisit(): void
    {
        $this->lastVisitedAt = new \DateTime();
    }

    public function wasVisitedAfter(\DateTime $date): bool
    {
        return $this->lastVisitedAt > $date;
    }
}
