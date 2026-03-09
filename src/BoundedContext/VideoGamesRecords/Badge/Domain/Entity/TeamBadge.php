<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository\TeamBadgeRepository;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;

#[ORM\Table(name:'vgr_team_badge')]
#[ORM\Entity(repositoryClass: TeamBadgeRepository::class)]
class TeamBadge
{
    use TimestampableEntity;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $endedAt = null;

    #[ORM\Column(nullable: true, options: ['default' => 0])]
    private ?int $mbOrder = null;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'teamBadge')]
    #[ORM\JoinColumn(name:'team_id', referencedColumnName:'id', nullable:false, onDelete: 'CASCADE')]
    private Team $team;

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

    public function setEndedAt(?DateTime $endedAt): static
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

    public function setTeam(Team $team): static
    {
        $this->team = $team;
        return $this;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }
}
