<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Doctrine\Repository\GameRepository;

#[ORM\Entity(repositoryClass: GameRepository::class)]
#[ORM\Table(name: 'igdb_game')]
class Game
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $storyline = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $checksum = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $firstReleaseDate = null;

    #[ORM\ManyToOne(targetEntity: Game::class)]
    #[ORM\JoinColumn(name: 'version_parent_id', referencedColumnName: 'id', nullable: true)]
    private ?Game $versionParent = null;

    /**
     * @var Collection<int, Genre>
     */
    #[ORM\ManyToMany(targetEntity: Genre::class)]
    #[ORM\JoinTable(
        name: 'igdb_game_genre',
        joinColumns: [new ORM\JoinColumn(name: 'game_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'genre_id', referencedColumnName: 'id')]
    )]
    private Collection $genres;

    /**
     * @var Collection<int, Platform>
     */
    #[ORM\ManyToMany(targetEntity: Platform::class)]
    #[ORM\JoinTable(
        name: 'igdb_game_platform',
        joinColumns: [new ORM\JoinColumn(name: 'game_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'platform_id', referencedColumnName: 'id')]
    )]
    private Collection $platforms;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->genres = new ArrayCollection();
        $this->platforms = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getStoryline(): ?string
    {
        return $this->storyline;
    }

    public function setStoryline(?string $storyline): void
    {
        $this->storyline = $storyline;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): void
    {
        $this->summary = $summary;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getChecksum(): ?string
    {
        return $this->checksum;
    }

    public function setChecksum(?string $checksum): void
    {
        $this->checksum = $checksum;
    }

    public function getFirstReleaseDate(): ?int
    {
        return $this->firstReleaseDate;
    }

    public function setFirstReleaseDate(?int $firstReleaseDate): void
    {
        $this->firstReleaseDate = $firstReleaseDate;
    }

    public function getFirstReleaseDateAsDateTime(): ?\DateTimeImmutable
    {
        return $this->firstReleaseDate ? new \DateTimeImmutable('@' . $this->firstReleaseDate) : null;
    }

    public function getVersionParent(): ?Game
    {
        return $this->versionParent;
    }

    public function setVersionParent(?Game $versionParent): void
    {
        $this->versionParent = $versionParent;
    }

    /**
     * @return Collection<int, Genre>
     */
    public function getGenres(): Collection
    {
        return $this->genres;
    }

    public function addGenre(Genre $genre): self
    {
        if (!$this->genres->contains($genre)) {
            $this->genres->add($genre);
        }

        return $this;
    }

    public function removeGenre(Genre $genre): self
    {
        $this->genres->removeElement($genre);

        return $this;
    }

    /**
     * @return Collection<int, Platform>
     */
    public function getPlatforms(): Collection
    {
        return $this->platforms;
    }

    public function addPlatform(Platform $platform): self
    {
        if (!$this->platforms->contains($platform)) {
            $this->platforms->add($platform);
        }

        return $this;
    }

    public function removePlatform(Platform $platform): self
    {
        $this->platforms->removeElement($platform);

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
