<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\Repository\TagRepository;

#[ORM\Table(name:'vgr_tag')]
#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Index(name: "idx_category", columns: ["category"])]
#[ORM\Index(name: "idx_is_official", columns: ["is_official"])]
class Tag
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[ORM\Column(length: 100, nullable: false)]
    private string $name;

    #[Assert\Length(max: 50)]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isOfficial = false;

    public function __toString(): string
    {
        return sprintf('%s [%s]', $this->getName(), $this->getId());
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setIsOfficial(bool $isOfficial): static
    {
        $this->isOfficial = $isOfficial;
        return $this;
    }

    public function isOfficial(): bool
    {
        return $this->isOfficial;
    }

    public function getIsOfficial(): bool
    {
        return $this->isOfficial;
    }
}
