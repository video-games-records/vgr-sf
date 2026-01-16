<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Intl\Locale;
use Symfony\Component\Validator\Constraints as Assert;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GroupRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\IsDlcTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\IsRankTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbPlayerTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbPostTrait;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\GroupOrderBy;

#[ORM\Table(name:'vgr_group')]
#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Index(name: "idx_lib_group_fr", columns: ["lib_group_fr"])]
#[ORM\Index(name: "idx_lib_group_en", columns: ["lib_group_en"])]
class Group
{
    use TimestampableEntity;
    use NbChartTrait;
    use NbPostTrait;
    use NbPlayerTrait;
    use IsRankTrait;
    use IsDlcTrait;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    protected ?int $id = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: false)]
    private string $libGroupEn = '';

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: false)]
    private string $libGroupFr = '';

    #[ORM\Column(length: 30, nullable: false, options: ['default' => GroupOrderBy::NAME])]
    private string $orderBy = GroupOrderBy::NAME;

    #[ORM\Column(length: 128)]
    #[Gedmo\Slug(fields: ['libGroupEn'])]
    protected string $slug;

    #[Assert\NotNull]
    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'groups')]
    #[ORM\JoinColumn(name:'game_id', referencedColumnName:'id', nullable:false)]
    private Game $game;

    /**
     * @var Collection<int, Chart>
     */
    #[ORM\OneToMany(targetEntity: Chart::class, cascade:['persist'], mappedBy: 'group')]
    private Collection $charts;


    public function __construct()
    {
        $this->charts = new ArrayCollection();
    }

    public function __toString()
    {
        return sprintf('%s [%s]', $this->getDefaultName(), $this->id);
    }

    public function getDefaultName(): string
    {
        return $this->libGroupEn;
    }

    public function getName(): ?string
    {
        $locale = Locale::getDefault();
        if ($locale == 'fr') {
            return $this->libGroupFr;
        } else {
            return $this->libGroupEn;
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

    public function setLibGroupEn(string $libGroupEn): void
    {
        $this->libGroupEn = $libGroupEn;
    }

    public function getLibGroupEn(): string
    {
        return $this->libGroupEn;
    }

    public function setLibGroupFr(?string $libGroupFr): void
    {
        if ($libGroupFr) {
            $this->libGroupFr = $libGroupFr;
        }
    }

    public function getLibGroupFr(): string
    {
        return $this->libGroupFr;
    }

    public function getGroupOrderBy(): GroupOrderBy
    {
        return new GroupOrderBy($this->orderBy);
    }

    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    public function setOrderBy(string $orderBy): void
    {
        $value = new GroupOrderBy($orderBy);
        $this->orderBy = $value->getValue();
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setGame(Game $game): void
    {
        $this->game = $game;
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function addChart(Chart $chart): void
    {
        $chart->setGroup($this);
        $this->charts[] = $chart;
    }

    public function removeChart(Chart $chart): void
    {
        $this->charts->removeElement($chart);
    }

    /**
     * @return Collection<int, Chart>
     */
    public function getCharts(): Collection
    {
        return $this->charts;
    }
}
