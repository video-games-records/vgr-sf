<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository\PlayerBadgeRepository;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;

#[ORM\Table(name:'vgr_player_badge')]
#[ORM\Entity(repositoryClass: PlayerBadgeRepository::class)]
class PlayerBadge
{
    use TimestampableEntity;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $endedAt = null;

    #[ORM\Column(nullable: true, options: ['default' => 0])]
    private ?int $mbOrder = null;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name:'player_id', referencedColumnName:'id', nullable:false, onDelete: 'CASCADE')]
    private Player $player;

    #[ORM\ManyToOne(targetEntity: Badge::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name:'badge_id', referencedColumnName:'id', nullable:false, onDelete: 'CASCADE')]
    private Badge $badge;


    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setEndedAt(DateTime $endedAt): static
    {
        $this->endedAt = $endedAt;
        return $this;
    }

    public function getEndedAt(): ?DateTime
    {
        return $this->endedAt;
    }

    public function setMbOrder(int $mbOrder): static
    {
        $this->mbOrder = $mbOrder;
        return $this;
    }

    public function getMbOrder(): ?int
    {
        return $this->mbOrder;
    }


    public function setBadge(Badge $badge): static
    {
        $this->badge = $badge;
        return $this;
    }

    public function getBadge(): Badge
    {
        return $this->badge;
    }

    public function setPlayer(Player $player): static
    {
        $this->player = $player;
        return $this;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function __toString(): string
    {
        return sprintf('%s # %s ', $this->getPlayer()->getPseudo(), $this->getBadge()->__toString());
    }
}
