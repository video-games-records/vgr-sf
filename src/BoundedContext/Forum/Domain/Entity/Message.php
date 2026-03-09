<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\MessageRepository;
use App\BoundedContext\User\Domain\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name:'pnf_message')]
#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    use TimestampableEntity;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[ORM\Column(type: 'text', nullable: false)]
    private string $message;

    #[ORM\Column(nullable: false, options: ['default' => 1])]
    private int $position = 1;

    #[ORM\ManyToOne(targetEntity: Topic::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(name:'topic_id', referencedColumnName:'id', nullable:false)]
    private Topic $topic;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name:'user_id', referencedColumnName:'id', nullable:false)]
    private User $user;

    public function __toString()
    {
        return sprintf('[%s]', $this->getId());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
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

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
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

    public function getPage(): int
    {
        return (int) floor(($this->getPosition() - 1) / 20) + 1;
    }

    public function getUrl(): string
    {
        return $this->getTopic()->getUrl() . '?page=' . $this->getPage() . '#' . $this->getId();
    }
}
