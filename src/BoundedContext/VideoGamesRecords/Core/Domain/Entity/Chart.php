<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\ChartRepository;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Proof;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\IsDlcTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbPostTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Intl\Locale;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name:'vgr_chart')]
#[ORM\Entity(repositoryClass: ChartRepository::class)]
#[ORM\Index(name: "idx_lib_chart_fr", columns: ["lib_chart_fr"])]
#[ORM\Index(name: "idx_lib_chart_en", columns: ["lib_chart_en"])]
class Chart
{
    use TimestampableEntity;
    use NbPostTrait;
    use IsDlcTrait;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: false)]
    private string $libChartEn = '';

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: false)]
    private string $libChartFr = '';


    #[ORM\Column(nullable: false, options: ['default' => false])]
    private bool $isProofVideoOnly = false;

    #[Assert\NotNull]
    #[ORM\ManyToOne(targetEntity: Group::class, inversedBy: 'charts')]
    #[ORM\JoinColumn(name:'group_id', referencedColumnName:'id', nullable:false, onDelete:'CASCADE')]
    private Group $group;

    /**
     * @var Collection<int, ChartLib>
     */
    #[ORM\OneToMany(targetEntity: ChartLib::class, cascade:['persist', 'remove'], mappedBy: 'chart', orphanRemoval: true)]
    private Collection $libs;

    /**
     * @var Collection<int, PlayerChart>
     */
    #[ORM\OneToMany(targetEntity: PlayerChart::class, mappedBy: 'chart', fetch: 'EXTRA_LAZY')]
    private Collection $playerCharts;

    /**
     * Shortcut to playerChart.rank = 1
     */
    private ?PlayerChart $playerChart1 = null;

    /**
     * Shortcut to playerChart.player = player
     */
    private ?PlayerChart $playerChartP = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Slug(fields: ['libChartEn'])]
    protected string $slug;

    /**
     * @var Collection<int, Proof>
     */
    #[ORM\OneToMany(targetEntity: Proof::class, cascade:['persist', 'remove'], mappedBy: 'chart', orphanRemoval: true)]
    private Collection $proofs;

    /**
     * @var Collection<int, LostPosition>
     */
    #[ORM\OneToMany(targetEntity: LostPosition::class, mappedBy: 'chart')]
    private Collection $lostPositions;

    public function __construct()
    {
        $this->libs = new ArrayCollection();
        $this->playerCharts = new ArrayCollection();
        $this->lostPositions = new ArrayCollection();
        $this->proofs = new ArrayCollection();
    }

    public function __toString()
    {
        return sprintf('%s [%s]', $this->getDefaultName(), $this->id);
    }

    public function getDefaultName(): ?string
    {
        return $this->libChartEn;
    }

    public function getName(): ?string
    {
        $locale = Locale::getDefault();
        if ($locale == 'fr') {
            return $this->libChartFr;
        } else {
            return $this->libChartEn;
        }
    }

    public function getCompleteName(string $locale = 'en'): string
    {
        if ($locale == 'fr') {
            return $this->getGroup()
                    ->getGame()
                    ->getLibGameFr() . ' - ' . $this->getGroup()
                    ->getLibGroupFr() . ' - ' . $this->getLibChartFr();
        } else {
            return $this->getGroup()
                    ->getGame()
                    ->getLibGameEn() . ' - ' . $this->getGroup()
                    ->getLibGroupEn() . ' - ' . $this->getLibChartEn();
        }
    }


    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function setLibChartEn(string $libChartEn): void
    {
        $this->libChartEn = $libChartEn;
    }

    public function getLibChartEn(): string
    {
        return $this->libChartEn;
    }

    public function setLibChartFr(?string $libChartFr): void
    {
        if ($libChartFr) {
            $this->libChartFr = $libChartFr;
        }
    }

    public function getLibChartFr(): string
    {
        return $this->libChartFr;
    }

    public function getIsProofVideoOnly(): bool
    {
        return $this->isProofVideoOnly;
    }

    public function setIsProofVideoOnly(bool $isProofVideoOnly): void
    {
        $this->isProofVideoOnly = $isProofVideoOnly;
    }

    /**
     * @return Collection<int, PlayerChart>
     */
    public function getPlayerCharts(): Collection
    {
        return $this->playerCharts;
    }

    public function addPlayerChart(PlayerChart $playerChart): void
    {
        $this->playerCharts->add($playerChart);
    }

    public function setGroup(Group $group): void
    {
        $this->group = $group;
    }

    public function getGroup(): Group
    {
        return $this->group;
    }

    public function addLib(ChartLib $lib): void
    {
        $lib->setChart($this);
        $this->libs[] = $lib;
    }

    public function removeLib(ChartLib $lib): void
    {
        $this->libs->removeElement($lib);
    }

    /**
     * @return Collection<int, ChartLib>
     */
    public function getLibs(): Collection
    {
        return $this->libs;
    }


    public function setPlayerChart1(?PlayerChart $playerChart1): void
    {
        $this->playerChart1 = $playerChart1;
    }

    public function getPlayerChart1(): ?PlayerChart
    {
        return $this->playerChart1;
    }

    public function setPlayerChartP(?PlayerChart $playerChartP): void
    {
        $this->playerChartP = $playerChartP;
    }

    public function getPlayerChartP(): ?PlayerChart
    {
        return $this->playerChartP;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getUrl(): string
    {
        return sprintf(
            '%s-game-g%d/%s-group-g%d/%s-chart-c%d/index',
            $this->getGroup()->getGame()->getSlug(),
            $this->getGroup()->getGame()->getId(),
            $this->getGroup()->getSlug(),
            $this->getGroup()->getId(),
            $this->getSlug(),
            $this->getId()
        );
    }

    /**
     * @return array<string>
     */
    public function getSluggableFields(): array
    {
        return ['defaultName'];
    }
}
