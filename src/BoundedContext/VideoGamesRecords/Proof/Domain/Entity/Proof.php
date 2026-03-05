<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\Repository\ProofRepository;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\ProofStatus;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;

#[ORM\Table(name:'vgr_proof')]
#[ORM\Entity(repositoryClass: ProofRepository::class)]
class Proof
{
    use TimestampableEntity;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Picture::class)]
    #[ORM\JoinColumn(name:'picture_id', referencedColumnName:'id', nullable:true)]
    private ?Picture $picture = null;

    #[ORM\ManyToOne(targetEntity: Video::class)]
    #[ORM\JoinColumn(name:'video_id', referencedColumnName:'id', nullable:true, onDelete: 'CASCADE')]
    private ?Video $video = null;

    #[ORM\ManyToOne(targetEntity: ProofRequest::class)]
    #[ORM\JoinColumn(name:'proof_request_id', referencedColumnName:'id', nullable:true)]
    private ?ProofRequest $proofRequest = null;

    #[Assert\Length(max: 30)]
    #[ORM\Column(length: 30, nullable: false)]
    private string $status = ProofStatus::IN_PROGRESS;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $response = null;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name:'responding_player_id', referencedColumnName:'id', nullable:true)]
    private ?Player $playerResponding = null;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name:'player_id', referencedColumnName:'id', nullable:false)]
    private Player $player;

    #[ORM\ManyToOne(targetEntity: Chart::class, inversedBy: 'proofs', fetch: 'EAGER')]
    #[ORM\JoinColumn(name:'chart_id', referencedColumnName:'id', nullable:false, onDelete:'CASCADE')]
    private Chart $chart;

    #[ORM\Column(nullable: true)]
    private ?DateTime $checkedAt = null;

    #[ORM\OneToOne(targetEntity: PlayerChart::class, mappedBy: 'proof')]
    private ?PlayerChart $playerChart = null;

    public function __toString()
    {
        return (string) $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setPicture(Picture $picture): void
    {
        $this->picture = $picture;
    }

    public function getPicture(): ?Picture
    {
        return $this->picture;
    }

    public function setVideo(Video $video): void
    {
        $this->video = $video;
    }

    public function getVideo(): ?Video
    {
        return $this->video;
    }

    public function setProofRequest(?ProofRequest $proofRequest): void
    {
        $this->proofRequest = $proofRequest;
    }

    public function getProofRequest(): ?ProofRequest
    {
        return $this->proofRequest;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): ProofStatus
    {
        return new ProofStatus($this->status);
    }

    public function setResponse(string $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setPlayerResponding(?Player $playerResponding = null): void
    {
        $this->playerResponding = $playerResponding;
    }

    public function getPlayerResponding(): ?Player
    {
        return $this->playerResponding;
    }

    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setChart(Chart $chart): void
    {
        $this->chart = $chart;
    }

    public function getChart(): Chart
    {
        return $this->chart;
    }

    public function setCheckedAt(DateTime $checkedAt): void
    {
        $this->checkedAt = $checkedAt;
    }

    public function getCheckedAt(): ?DateTime
    {
        return $this->checkedAt;
    }

    public function getPlayerChart(): ?PlayerChart
    {
        return $this->playerChart;
    }

    public function getType(): string
    {
        return ($this->getPicture() != null) ? 'Picture' : 'Video';
    }
}
