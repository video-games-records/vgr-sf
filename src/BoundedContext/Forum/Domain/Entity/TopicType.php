<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\TopicTypeRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name:'pnf_topic_type')]
#[ORM\Entity(repositoryClass: TopicTypeRepository::class)]
class TopicType
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\Length(max: 30)]
    #[ORM\Column(length: 30, nullable: false)]
    private string $name;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $position = 0;

    public function __toString()
    {
        return sprintf('%s [%s]', $this->getName(), $this->getId());
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

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getPosition(): int
    {
        return $this->position;
    }
}
