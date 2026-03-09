<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlatformRepository;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlatformBadge;

#[ORM\Table(name:'vgr_platform')]
#[ORM\Entity(repositoryClass: PlatformRepository::class)]
class Platform
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\NotNull]
    #[Assert\Length(max: 100)]
    #[ORM\Column(length: 100, nullable: false)]
    private string $name = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    #[Assert\Length(max: 30)]
    #[ORM\Column(length: 30, nullable: false)]
    private string $status = 'INACTIF';

    #[ORM\Column(length: 255)]
    #[Gedmo\Slug(fields: ['name'])]
    protected string $slug;


    /**
     * @var Collection<int, Game>
     */
    #[Orm\ManyToMany(targetEntity: Game::class, mappedBy: 'platforms')]
    private Collection $games;


    #[ORM\OneToOne(targetEntity: PlatformBadge::class, cascade: ['persist'], inversedBy: 'platform')]
    #[ORM\JoinColumn(name:'badge_id', referencedColumnName:'id', nullable:true)]
    private ?PlatformBadge $badge = null;

    public function __construct()
    {
        $this->games = new ArrayCollection();
    }


    public function __toString()
    {
        return sprintf('%s [%s]', $this->name, $this->id);
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;
        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return Collection<int, Game>
     */
    public function getGames(): Collection
    {
        return $this->games;
    }

    public function setBadge(?PlatformBadge $badge = null): static
    {
        $this->badge = $badge;
        return $this;
    }

    public function getBadge(): ?PlatformBadge
    {
        return $this->badge;
    }
}
