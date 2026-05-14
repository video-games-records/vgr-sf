<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Infrastructure\Doctrine\Repository\PlatformConnectionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'player_platform_connection')]
#[ORM\Entity(repositoryClass: PlatformConnectionRepository::class)]
#[ORM\UniqueConstraint(name: 'uq_player_platform', columns: ['player_id', 'platform'])]
class PlatformConnection
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Player $player;

    #[ORM\Column(length: 50, enumType: PlatformEnum::class)]
    private PlatformEnum $platform;

    #[ORM\Column(length: 255)]
    private string $externalId;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $username = null;

    #[ORM\Column]
    private \DateTimeImmutable $linkedAt;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $tokenData = null;

    public function __construct(
        Player $player,
        PlatformEnum $platform,
        string $externalId,
        ?string $username = null,
    ) {
        $this->player = $player;
        $this->platform = $platform;
        $this->externalId = $externalId;
        $this->username = $username;
        $this->linkedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getPlatform(): PlatformEnum
    {
        return $this->platform;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getLinkedAt(): \DateTimeImmutable
    {
        return $this->linkedAt;
    }

    public function updateUsername(?string $username): void
    {
        $this->username = $username;
    }

    public function getTokenData(): ?string
    {
        return $this->tokenData;
    }

    public function setTokenData(?string $tokenData): void
    {
        $this->tokenData = $tokenData;
    }
}
