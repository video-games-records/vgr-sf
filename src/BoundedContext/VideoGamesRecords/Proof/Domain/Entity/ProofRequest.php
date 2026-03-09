<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\Repository\ProofRequestRepository;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\ProofRequestStatus;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;

#[ORM\Table(name:'vgr_proof_request')]
#[ORM\Entity(repositoryClass: ProofRequestRepository::class)]
class ProofRequest
{
    use TimestampableEntity;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\Length(max: 50)]
    #[ORM\Column(length: 50, nullable: false)]
    private string $status = ProofRequestStatus::IN_PROGRESS;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $response = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $message = null;

    #[ORM\Column(nullable: true)]
    private ?Datetime $dateAcceptance = null;

    #[ORM\ManyToOne(targetEntity: PlayerChart::class)]
    #[ORM\JoinColumn(name:'player_chart_id', referencedColumnName:'id', nullable:false, onDelete:'CASCADE')]
    private PlayerChart $playerChart;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name:'requesting_player_id', referencedColumnName:'id', nullable:false)]
    private Player $playerRequesting;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name:'responding_player_id', referencedColumnName:'id', nullable:true)]
    private ?Player $playerResponding = null;

    public function __toString()
    {
        return sprintf('Request [%s]', $this->id);
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

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setResponse(string $response): static
    {
        $this->response = $response;
        return $this;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setDateAcceptance(DateTime $dateAcceptance): static
    {
        $this->dateAcceptance = $dateAcceptance;
        return $this;
    }

    public function getDateAcceptance(): ?DateTime
    {
        return $this->dateAcceptance;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setPlayerChart(PlayerChart $playerChart): static
    {
        $this->playerChart = $playerChart;
        return $this;
    }

    public function getPlayerChart(): PlayerChart
    {
        return $this->playerChart;
    }

    public function setPlayerRequesting(Player $playerRequesting): static
    {
        $this->playerRequesting = $playerRequesting;
        return $this;
    }

    public function getPlayerRequesting(): Player
    {
        return $this->playerRequesting;
    }

    public function setPlayerResponding(?Player $playerResponding = null): static
    {
        $this->playerResponding = $playerResponding;
        return $this;
    }

    public function getPlayerResponding(): ?Player
    {
        return $this->playerResponding;
    }
}
