<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Domain\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\TopicRepository;
use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name:'pnf_topic')]
#[ORM\Entity(repositoryClass: TopicRepository::class)]
#[ORM\Index(name: "idx_name", columns: ["name"])]
class Topic
{
    use TimestampableEntity;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(min:3, max: 255)]
    #[ORM\Column(length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $nbMessage = 0;

    #[ORM\ManyToOne(targetEntity: Forum::class, inversedBy: 'topics')]
    #[ORM\JoinColumn(name:'forum_id', referencedColumnName:'id', nullable:false)]
    private Forum $forum;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name:'user_id', referencedColumnName:'id', nullable:false)]
    private User $user;

    #[ORM\Column(length: 255)]
    #[Gedmo\Slug(fields: ['name'])]
    protected string $slug;

    #[ORM\ManyToOne(targetEntity: TopicType::class)]
    #[ORM\JoinColumn(name:'type_id', referencedColumnName:'id', nullable:true)]
    private ?TopicType $type = null;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'topic', cascade: ['persist'])]
    private Collection $messages;

    #[ORM\ManyToOne(targetEntity: Message::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name:'max_message_id', referencedColumnName:'id', nullable:true, onDelete: 'SET NULL')]
    private ?Message $lastMessage;

    #[ORM\Column(nullable: false, options: ['default' => false])]
    private bool $boolArchive = false;

    /**
     * @var Collection<int, TopicUserLastVisit>
     */
    #[ORM\OneToMany(targetEntity: TopicUserLastVisit::class, mappedBy: 'topic')]
    private Collection $userLastVisits;

    public ?bool $hasNewContent = null;

    public function __toString()
    {
        return sprintf('%s [%s]', $this->getName(), $this->getId());
    }

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->userLastVisits = new ArrayCollection();
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setNbMessage(int $nbMessage): void
    {
        $this->nbMessage = $nbMessage;
    }

    public function getNbMessage(): int
    {
        return $this->nbMessage;
    }

    public function setForum(Forum $forum): void
    {
        $this->forum = $forum;
    }

    public function getForum(): Forum
    {
        return $this->forum;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setType(?TopicType $type): void
    {
        $this->type = $type;
    }

    public function getType(): ?TopicType
    {
        return $this->type;
    }

    /**
     * @param array<Message> $messages
     */
    public function setMessages(array $messages): void
    {
        foreach ($messages as $message) {
            $this->addMessage($message);
        }
    }

    public function addMessage(Message $message): void
    {
        $message->setTopic($this);
        $this->messages[] = $message;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function setLastMessage(?Message $message = null): void
    {
        $this->lastMessage = $message;
    }

    public function getLastMessage(): ?Message
    {
        return $this->lastMessage;
    }

    public function setBoolArchive(bool $boolArchive): void
    {
        $this->boolArchive = $boolArchive;
    }

    public function getBoolArchive(): bool
    {
        return $this->boolArchive;
    }

    public function getLastVisitData(): ?TopicUserLastVisit
    {
        if ($this->userLastVisits->first()) {
            return $this->userLastVisits->first();
        }
        return null;
    }

    public function getIsRead(): ?bool
    {
        $topicVisit = $this->getLastVisitData();
        if ($topicVisit && $this->getLastMessage()) {
            return $topicVisit->getLastVisitedAt() >= $this->getLastMessage()->getCreatedAt();
        } else {
            return $this->getLastMessage() === null;
        }
    }

    public function hasNewContent(): ?bool
    {
        $topicVisit = $this->getLastVisitData();
        if ($topicVisit && $this->getLastMessage()) {
            return !$this->getIsRead();
        } else {
            return $this->getLastMessage() !== null;
        }
    }

    public function getHasBeenVisited(): ?bool
    {
        $topicVisit = $this->getLastVisitData();
        return $topicVisit !== null;
    }

    public function getLastVisitedAt(): ?\DateTime
    {
        $topicVisit = $this->getLastVisitData();
        return $topicVisit?->getLastVisitedAt();
    }

    public function getIsNotify(): ?bool
    {
        $topicVisit = $this->getLastVisitData();
        return $topicVisit && $topicVisit->getIsNotify();
    }

    public function getUrl(): string
    {
        return sprintf(
            '%s-forum-f%d/%s-topic-t%d/index',
            $this->getForum()->getSlug(),
            $this->getForum()->getId(),
            $this->getSlug(),
            $this->getId()
        );
    }
}
