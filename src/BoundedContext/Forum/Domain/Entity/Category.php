<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\CategoryRepository;

#[ORM\Table(name:'pnf_category')]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    use TimestampableEntity;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: false)]
    private string $name;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $position = 0;

    #[ORM\Column(nullable: false, options: ['default' => true])]
    private bool $displayOnHome = true;

    /**
     * @var Collection<int, Forum>
     */
    #[ORM\OneToMany(targetEntity: Forum::class, mappedBy: 'category')]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $forums;

    public function __toString()
    {
        return sprintf('%s [%s]', $this->getName(), $this->getId());
    }

    public function __construct()
    {
        $this->forums = new ArrayCollection();
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

    public function setPosition(int $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setDisplayOnHome(bool $displayOnHome): static
    {
        $this->displayOnHome = $displayOnHome;
        return $this;
    }

    public function getDisplayOnHome(): bool
    {
        return $this->displayOnHome;
    }

    /**
     * @return Collection<int, Forum>
     */
    public function getForums(): Collection
    {
        return $this->forums;
    }
}
