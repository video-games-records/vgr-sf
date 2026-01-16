<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamRequestRepository;
use App\BoundedContext\VideoGamesRecords\Team\Domain\ValueObject\TeamRequestStatus;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;

#[ORM\Table(name:'vgr_team_request')]
#[ORM\Entity(repositoryClass: TeamRequestRepository::class)]
class TeamRequest
{
    use TimestampableEntity;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\Length(max: 30)]
    #[ORM\Column(length: 30, nullable: false)]
    private string $status = TeamRequestStatus::ACTIVE;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name:'team_id', referencedColumnName:'id', nullable:false, onDelete: 'CASCADE')]
    private Team $team;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name:'player_id', referencedColumnName:'id', nullable:false, onDelete: 'CASCADE')]
    private Player $player;

    public function __toString()
    {
        return sprintf('%s # %s [%s]', $this->getTeam()->getLibTeam(), $this->getPlayer()->getPseudo(), $this->id);
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setStatus(string $status): void
    {
        $value = new TeamRequestStatus($status);
        $this->status = $value->getValue();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTeamRequestStatus(): TeamRequestStatus
    {
        return new TeamRequestStatus($this->status);
    }

    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }
}
