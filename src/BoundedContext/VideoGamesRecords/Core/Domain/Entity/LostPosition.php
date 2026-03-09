<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\LostPositionRepository;

#[ORM\Table(name:'vgr_lostposition')]
#[ORM\Entity(repositoryClass: LostPositionRepository::class)]
class LostPosition
{
    use TimestampableEntity;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $oldRank = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $newRank = 0;

    #[Assert\NotNull]
    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name:'player_id', referencedColumnName:'id', nullable:false, onDelete: 'CASCADE')]
    private Player $player;

    #[ORM\ManyToOne(targetEntity: Chart::class, inversedBy: 'lostPositions')]
    #[ORM\JoinColumn(name:'chart_id', referencedColumnName:'id', nullable:false, onDelete:'CASCADE')]
    private Chart $chart;


    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setNewRank(int $newRank): static
    {
        $this->newRank = $newRank;
        return $this;
    }

    public function getNewRank(): int
    {
        return $this->newRank;
    }

    public function setOldRank(int $oldRank): static
    {
        $this->oldRank = $oldRank;
        return $this;
    }

    public function getOldRank(): int
    {
        return $this->oldRank;
    }

    public function setChart(Chart $chart): static
    {
        $this->chart = $chart;
        return $this;
    }

    public function getChart(): Chart
    {
        return $this->chart;
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
}
