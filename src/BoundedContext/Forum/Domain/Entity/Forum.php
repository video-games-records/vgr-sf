<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\ForumRepository;
use App\BoundedContext\Forum\Domain\ValueObject\ForumStatus;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name:'pnf_forum')]
#[ORM\Entity(repositoryClass: ForumRepository::class)]
#[ORM\Index(name: "idx_position", columns: ["position"])]
#[ORM\Index(name: "idx_lib_forum", columns: ["lib_forum"])]
class Forum
{
    use TimestampableEntity;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: false)]
    private string $libForum;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $libForumFr = null;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $position = 0;

    #[ORM\Column(length: 20, nullable: false)]
    private string $status = ForumStatus::PUBLIC;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $role = null;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $nbMessage = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $nbTopic = 0;

    #[ORM\Column(length: 128)]
    #[Gedmo\Slug(fields: ['libForum'])]
    protected string $slug;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'forums')]
    #[ORM\JoinColumn(name:'category_id', referencedColumnName:'id', nullable:true)]
    private ?Category $category = null;

    /**
     * @var Collection<int, Topic>
     */
    #[ORM\OneToMany(targetEntity: Topic::class, mappedBy: 'forum')]
    private Collection $topics;

    #[ORM\ManyToOne(targetEntity: Message::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name:'max_message_id', referencedColumnName:'id', nullable:true, onDelete: 'SET NULL')]
    private ?Message $lastMessage = null;

    /**
     * @var Collection<int, ForumUserLastVisit>
     */
    #[ORM\OneToMany(targetEntity: ForumUserLastVisit::class, mappedBy: 'forum')]
    private Collection $userLastVisits;

    public ?int $unreadTopicsCount = null;

    public ?bool $isUnread = null;

    public ?bool $hasNewContent = null;

    public ?bool $hasBeenVisited = null;

    public function __construct()
    {
        $this->topics = new ArrayCollection();
        $this->userLastVisits = new ArrayCollection();
    }

    public function __toString()
    {
        return sprintf('%s [%s]', $this->getLibForum(), $this->getId());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setLibForum(string $libForum): static
    {
        $this->libForum = $libForum;
        return $this;
    }

    public function getLibForum(): string
    {
        return $this->libForum;
    }

    public function setLibForumFr(string $libForumFr): static
    {
        $this->libForumFr = $libForumFr;
        return $this;
    }

    public function getLibForumFr(): ?string
    {
        return $this->libForumFr;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setNbMessage(int $nbMessage): static
    {
        $this->nbMessage = $nbMessage;
        return $this;
    }

    public function getNbMessage(): int
    {
        return $this->nbMessage;
    }

    public function setNbTopic(int $nbTopic): static
    {
        $this->nbTopic = $nbTopic;
        return $this;
    }

    public function getNbTopic(): int
    {
        return $this->nbTopic;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setCategory(?Category $category = null): static
    {
        $this->category = $category;
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @return Collection<int, Topic>
     */
    public function getTopics(): Collection
    {
        return $this->topics;
    }

    public function setLastMessage(?Message $message = null): static
    {
        $this->lastMessage = $message;
        return $this;
    }

    public function getLastMessage(): ?Message
    {
        return $this->lastMessage;
    }

    public function getLastVisitData(): ?ForumUserLastVisit
    {
        if ($this->userLastVisits->first()) {
            return $this->userLastVisits->first();
        }
        return null;
    }

    public function getHasNewContent(): ?bool
    {
        $forumVisit = $this->getLastVisitData();
        if ($forumVisit && $this->getLastMessage()) {
            return $this->getLastMessage()->getCreatedAt() > $forumVisit->getLastVisitedAt();
        } else {
            return $this->getLastMessage() !== null;
        }
    }

    public function getHasBeenVisited(): ?bool
    {
        $forumVisit = $this->getLastVisitData();
        return $forumVisit !== null;
    }

    public function getLastVisitedAt(): ?\DateTime
    {
        $forumVisit = $this->getLastVisitData();
        return $forumVisit?->getLastVisitedAt();
    }
}
