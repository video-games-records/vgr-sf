<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Domain\Entity;

use App\BoundedContext\Forum\Domain\Entity\Forum;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\TeamBadge;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\AverageChartRankTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\AverageGameRankTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank0Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank1Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank2Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank3Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank4Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ChartRank5Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\GameRank0Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\GameRank1Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\GameRank2Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\GameRank3Trait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbGameTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbMasterBadgeTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbPlayerTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PointBadgeTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PointChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PointGameTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankCupTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankMedalTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankPointBadgeTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankPointChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankPointGameTrait;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name:'vgr_team')]
#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    use TimestampableEntity;
    use RankCupTrait;
    use GameRank0Trait;
    use GameRank1Trait;
    use GameRank2Trait;
    use GameRank3Trait;
    use RankMedalTrait;
    use ChartRank0Trait;
    use ChartRank1Trait;
    use ChartRank2Trait;
    use ChartRank3Trait;
    use ChartRank4Trait;
    use ChartRank5Trait;
    use RankPointBadgeTrait;
    use PointBadgeTrait;
    use RankPointGameTrait;
    use PointGameTrait;
    use RankPointChartTrait;
    use PointChartTrait;
    use AverageChartRankTrait;
    use AverageGameRankTrait;
    use NbPlayerTrait;
    use NbGameTrait;
    use NbMasterBadgeTrait;

    public const string STATUS_OPENED = 'OPENED';
    public const string STATUS_CLOSED = 'CLOSED';

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private int $id;

    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 50)]
    #[ORM\Column(length: 50, nullable: false)]
    private string $libTeam;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 4)]
    #[ORM\Column(length: 10, nullable: false)]
    private string $tag;

    #[Assert\Length(max: 255)]
    #[Assert\Url(protocols: ['https', 'http'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $siteWeb = null;

    #[Assert\Length(max: 30)]
    #[ORM\Column(length: 30, nullable: false, options: ['default' => 'default.png'])]
    private string $logo = 'default.png';


    #[ORM\Column(type: 'text', length: 30, nullable: true)]
    private ?string $presentation = null;


    #[Assert\Choice(choices: ['CLOSED', 'OPENED'])]
    #[ORM\Column(length: 30, nullable: false)]
    private string $status = self::STATUS_CLOSED;

    #[ORM\Column(length: 128)]
    #[Gedmo\Slug(fields: ['libTeam'])]
    protected string $slug;

    /**
     * @var Collection<int, Player>
     */
    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'team')]
    #[ORM\OrderBy(["pseudo" => "ASC"])]
    private Collection $players;

    /**
     * @var Collection<int, TeamGame>
     */
    #[ORM\OneToMany(targetEntity: TeamGame::class, mappedBy: 'team')]
    private Collection $teamGame;

    /**
     * @var Collection<int, TeamBadge>
     */
    #[ORM\OneToMany(targetEntity: TeamBadge::class, mappedBy: 'team')]
    private Collection $teamBadge;


    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name:'leader_id', referencedColumnName:'id', nullable:false)]
    private Player $leader;

    #[ORM\OneToOne(targetEntity: Forum::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name:'forum_id', referencedColumnName:'id', nullable:true, onDelete: 'SET NULL')]
    private ?Forum $forum = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->teamGame = new ArrayCollection();
        $this->teamBadge = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setLibTeam(string $libTeam): static
    {
        $this->libTeam = $libTeam;
        return $this;
    }

    public function getLibTeam(): string
    {
        return $this->libTeam;
    }

    public function getName(): string
    {
        return $this->libTeam;
    }

    public function setTag(string $tag): static
    {
        $this->tag = $tag;
        return $this;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function setLeader(Player $leader): static
    {
        $this->leader = $leader;
        return $this;
    }

    public function getLeader(): Player
    {
        return $this->leader;
    }

    public function getForum(): ?Forum
    {
        return $this->forum;
    }

    public function setForum(?Forum $forum): static
    {
        $this->forum = $forum;
        return $this;
    }

    public function setSiteWeb(?string $siteWeb): static
    {
        $this->siteWeb = $siteWeb;
        return $this;
    }


    public function getSiteWeb(): ?string
    {
        return $this->siteWeb;
    }


    public function setLogo(string $logo): static
    {
        $this->logo = $logo;
        return $this;
    }

    public function getLogo(): string
    {
        return $this->logo;
    }

    public function setPresentation(string $presentation): static
    {
        $this->presentation = $presentation;
        return $this;
    }

    public function getPresentation(): ?string
    {
        return $this->presentation;
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return Collection<int, Player>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    /**
     * @return Collection<int, TeamGame>
     */
    public function getTeamGame(): Collection
    {
        return $this->teamGame;
    }

    /**
     * @return Collection<int, TeamBadge>
     */
    public function getTeamBadge(): Collection
    {
        return $this->teamBadge;
    }

    public function isOpened(): bool
    {
        return ($this->getStatus() == self::STATUS_OPENED);
    }

    /**
     * @return array<string>
     */
    public function getSluggableFields(): array
    {
        return ['libTeam'];
    }

    /**
     * @return array<string, string>
     */
    public static function getStatusChoices(): array
    {
        return [
            self::STATUS_CLOSED => self::STATUS_CLOSED,
            self::STATUS_OPENED => self::STATUS_OPENED,
        ];
    }
}
