<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Proof;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\LastUpdateTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbEqualTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

#[ORM\Table(name:'vgr_player_chart')]
#[ORM\Entity(repositoryClass: PlayerChartRepository::class)]
#[ORM\UniqueConstraint(name: "unq_player_chart", columns: ["player_id", "chart_id"])]
#[ORM\Index(name: "idx_rank", columns: ["`rank`"])]
#[ORM\Index(name: "idx_point_chart", columns: ["point_chart"])]
#[ORM\Index(name: "idx_top_score", columns: ["is_top_score"])]
#[ORM\Index(name: "idx_last_update_player", columns: ["last_update", 'player_id'])]
#[ORM\Index(name: "idx_player_chart_last_update", columns: ["last_update"])]
#[ORM\Index(name: "idx_status", columns: ["status"])]
#[ORM\Index(name: "idx_player_status", columns: ["player_id", "status"])]
#[DoctrineAssert\UniqueEntity(fields: ['chart', 'player'], message: "A score already exists")]
class PlayerChart
{
    use TimestampableEntity;
    use NbEqualTrait;
    use LastUpdateTrait;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(name: '`rank`', nullable: true)]
    private ?int $rank = null;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $pointChart = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $pointPlatform = 0;

    #[ORM\Column(nullable: false, options: ['default' => false])]
    private bool $isTopScore = false;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTime $dateInvestigation = null;

    #[ORM\ManyToOne(targetEntity: Chart::class, inversedBy: 'playerCharts', fetch: 'EAGER')]
    #[ORM\JoinColumn(name:'chart_id', referencedColumnName:'id', nullable:false, onDelete:'CASCADE')]
    private Chart $chart;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'playerCharts')]
    #[ORM\JoinColumn(name:'player_id', referencedColumnName:'id', nullable:false)]
    private Player $player;

    #[ORM\OneToOne(targetEntity: Proof::class, inversedBy: 'playerChart')]
    #[ORM\JoinColumn(name:'proof_id', referencedColumnName:'id', nullable:true, onDelete:'SET NULL')]
    private ?Proof $proof = null;

    #[ORM\Column(enumType: PlayerChartStatusEnum::class)]
    private PlayerChartStatusEnum $status = PlayerChartStatusEnum::NONE;

    #[ORM\ManyToOne(targetEntity: Platform::class)]
    #[ORM\JoinColumn(name:'platform_id', referencedColumnName:'id', nullable:true)]
    private ?Platform $platform = null;

    /**
     * @var Collection<int, PlayerChartLib>
     */
    #[ORM\OneToMany(
        mappedBy: 'playerChart',
        targetEntity: PlayerChartLib::class,
        cascade: ['persist', 'remove'],
        fetch: 'EAGER',
        orphanRemoval: true
    )]
    private Collection $libs;

    public function __construct()
    {
        $this->libs = new ArrayCollection();
    }

    public function __toString()
    {
        return sprintf('%s # %s [%s]', $this->getChart()->getDefaultName(), $this->getPlayer()->getPseudo(), $this->id);
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setRank(int $rank): void
    {
        $this->rank = $rank;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setPointChart(int $pointChart): void
    {
        $this->pointChart = $pointChart;
    }

    public function getPointChart(): int
    {
        return $this->pointChart;
    }

    public function setPointPlatform(int $pointPlatform): void
    {
        $this->pointPlatform = $pointPlatform;
    }

    public function getPointPlatform(): ?int
    {
        return $this->pointPlatform;
    }

    public function getIsTopScore(): bool
    {
        return $this->isTopScore;
    }

    public function setIsTopScore(bool $isTopScore): void
    {
        $this->isTopScore = $isTopScore;
    }

    public function setDateInvestigation(?DateTime $dateInvestigation = null): void
    {
        $this->dateInvestigation = $dateInvestigation;
    }

    public function getDateInvestigation(): ?DateTime
    {
        return $this->dateInvestigation;
    }

    public function setChart(Chart $chart): void
    {
        $this->chart = $chart;
    }

    public function getChart(): Chart
    {
        return $this->chart;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    public function setProof(?Proof $proof = null): void
    {
        $this->proof = $proof;
    }

    public function getProof(): ?Proof
    {
        return $this->proof;
    }

    public function setPlatform(?Platform $platform = null): void
    {
        $this->platform = $platform;
    }

    public function getPlatform(): ?Platform
    {
        return $this->platform;
    }

    public function setStatus(PlayerChartStatusEnum $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): PlayerChartStatusEnum
    {
        return $this->status;
    }

    public function addLib(PlayerChartLib $lib): void
    {
        $lib->setPlayerChart($this);
        $this->libs[] = $lib;
    }

    public function removeLib(PlayerChartLib $lib): void
    {
        $this->libs->removeElement($lib);
    }

    /**
     * @return Collection<int, PlayerChartLib>
     */
    public function getLibs(): Collection
    {
        return $this->libs;
    }


    public function getValuesAsString(): string
    {
        $values = [];
        foreach ($this->getLibs() as $lib) {
            $values[] = $lib->getValue();
        }
        return implode('|', $values);
    }

    public function getUrl(): string
    {
        return sprintf(
            '%s-game-g%d/%s-group-g%d/%s-chart-c%d/pc-%d/index',
            $this->getChart()->getGroup()->getGame()->getSlug(),
            $this->getChart()->getGroup()->getGame()->getId(),
            $this->getChart()->getGroup()->getSlug(),
            $this->getChart()->getGroup()->getId(),
            $this->getChart()->getSlug(),
            $this->getChart()->getId(),
            $this->getId()
        );
    }
}
