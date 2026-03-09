<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Doctrine\Repository\PlatformRepository;

#[ORM\Entity(repositoryClass: PlatformRepository::class)]
#[ORM\Table(name: 'igdb_platform')]
class Platform
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $abbreviation = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $alternativeName = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $generation = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $checksum = null;

    #[ORM\ManyToOne(targetEntity: PlatformType::class)]
    #[ORM\JoinColumn(name: 'platform_type_id', referencedColumnName: 'id', nullable: true)]
    private ?PlatformType $platformType = null;

    #[ORM\ManyToOne(targetEntity: PlatformLogo::class)]
    #[ORM\JoinColumn(name: 'platform_logo_id', referencedColumnName: 'id', nullable: true)]
    private ?PlatformLogo $platformLogo = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
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

    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(?string $abbreviation): static
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    public function getAlternativeName(): ?string
    {
        return $this->alternativeName;
    }

    public function setAlternativeName(?string $alternativeName): static
    {
        $this->alternativeName = $alternativeName;
        return $this;
    }

    public function getGeneration(): ?int
    {
        return $this->generation;
    }

    public function setGeneration(?int $generation): static
    {
        $this->generation = $generation;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): static
    {
        $this->summary = $summary;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function getChecksum(): ?string
    {
        return $this->checksum;
    }

    public function setChecksum(?string $checksum): static
    {
        $this->checksum = $checksum;
        return $this;
    }

    public function getPlatformType(): ?PlatformType
    {
        return $this->platformType;
    }

    public function setPlatformType(?PlatformType $platformType): static
    {
        $this->platformType = $platformType;
        return $this;
    }

    public function getPlatformLogo(): ?PlatformLogo
    {
        return $this->platformLogo;
    }

    public function setPlatformLogo(?PlatformLogo $platformLogo): static
    {
        $this->platformLogo = $platformLogo;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name . ($this->abbreviation ? ' (' . $this->abbreviation . ')' : '');
    }
}
