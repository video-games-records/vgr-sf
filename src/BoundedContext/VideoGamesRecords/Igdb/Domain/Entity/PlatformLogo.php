<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Doctrine\Repository\PlatformLogoRepository;

#[ORM\Entity(repositoryClass: PlatformLogoRepository::class)]
#[ORM\Table(name: 'igdb_platform_logo')]
class PlatformLogo
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'boolean')]
    private bool $alphaChannel;

    #[ORM\Column(type: 'boolean')]
    private bool $animated;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $checksum = null;

    #[ORM\Column(type: 'integer')]
    private int $height;

    #[ORM\Column(type: 'string', length: 255)]
    private string $imageId;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(type: 'integer')]
    private int $width;

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

    public function isAlphaChannel(): bool
    {
        return $this->alphaChannel;
    }

    public function setAlphaChannel(bool $alphaChannel): static
    {
        $this->alphaChannel = $alphaChannel;
        return $this;
    }

    public function isAnimated(): bool
    {
        return $this->animated;
    }

    public function setAnimated(bool $animated): static
    {
        $this->animated = $animated;
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

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): static
    {
        $this->height = $height;
        return $this;
    }

    public function getImageId(): string
    {
        return $this->imageId;
    }

    public function setImageId(string $imageId): static
    {
        $this->imageId = $imageId;
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

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): static
    {
        $this->width = $width;
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

    public function getImageUrl(string $size = 'logo_med'): string
    {
        return "https://images.igdb.com/igdb/image/upload/t_{$size}/{$this->imageId}.png";
    }

    public function __toString(): string
    {
        return $this->imageId . ' (' . $this->width . 'x' . $this->height . ')';
    }
}
