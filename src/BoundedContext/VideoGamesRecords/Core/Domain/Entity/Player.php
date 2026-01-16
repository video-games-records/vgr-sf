<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\AverageChartRankTrait;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerStatusEnum;
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
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbChartProvenTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbGameTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbMasterBadgeTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbVideoTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\Player\PlayerCommunicationDataTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\Player\PlayerPersonalDataTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PointBadgeTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PointChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PointGameTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankCupTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankMedalTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankPointBadgeTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankPointChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\RankPointGameTrait;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Proof;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;

#[ORM\Table(name:'vgr_player')]
#[ORM\Entity(repositoryClass: PlayerRepository::class)]
#[ORM\Index(name: "idx_point_game", columns: ["point_game"])]
#[ORM\Index(name: "idx_chart_rank", columns: ["chart_rank0", "chart_rank1", "chart_rank2", "chart_rank3"])]
#[ORM\Index(name: "idx_game_rank", columns: ["game_rank0", "game_rank1", "game_rank2", "game_rank3"])]
class Player
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
    use RankPointChartTrait;
    use PointChartTrait;
    use RankPointGameTrait;
    use PointGameTrait;
    use AverageChartRankTrait;
    use AverageGameRankTrait;
    use PlayerCommunicationDataTrait;
    use PlayerPersonalDataTrait;
    use NbChartTrait;
    use NbChartProvenTrait;
    use NbGameTrait;
    use NbVideoTrait;
    use NbMasterBadgeTrait;

    #[ORM\Column(nullable: false)]
    private int $user_id;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\Length(min: 3, max: 50)]
    #[ORM\Column(length: 50, nullable: false, unique: true)]
    private string $pseudo;

    #[Assert\Length(max: 100)]
    #[ORM\Column(length: 100, nullable: false, options: ['default' => "default.jpg"])]
    private string $avatar = 'default.jpg';

    #[Assert\Length(max: 50)]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $gamerCard = null;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $rankProof = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $rankCountry = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $nbChartMax = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $nbChartWithPlatform = 0;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $nbChartDisabled = 0;

    #[ORM\Column(nullable: true)]
    protected ?DateTime $lastLogin = null;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    protected int $nbConnexion = 0;

    #[ORM\Column(nullable: false, options: ['default' => false])]
    private bool $boolMaj = false;

    #[ORM\Column(nullable: false, options: ['default' => false])]
    private bool $hasDonate = false;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'players')]
    #[ORM\JoinColumn(name:'team_id', referencedColumnName:'id', nullable:true, onDelete: 'SET NULL')]
    private ?Team $team = null;

    #[ORM\Column(nullable: true)]
    protected ?DateTime $lastDisplayLostPosition;

    #[ORM\Column(enumType: PlayerStatusEnum::class)]
    private PlayerStatusEnum $status;

    #[ORM\Column(length: 128)]
    #[Gedmo\Slug(fields: ['pseudo'])]
    protected string $slug;


    /**
     * @var Collection<int, PlayerGame>
     */
    #[ORM\OneToMany(targetEntity: PlayerGame::class, mappedBy: 'player')]
    private Collection $playerGame;

    /**
     * @var Collection<int, PlayerChart>
     */
    #[ORM\OneToMany(targetEntity: PlayerChart::class, mappedBy: 'player')]
    private Collection $playerCharts;

    /**
     * @var Collection<int, Player>
     */
    #[ORM\ManyToMany(targetEntity: Player::class)]
    #[ORM\JoinTable(name: 'vgr_friend')]
    #[ORM\JoinColumn(name: 'player_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'friend_id', referencedColumnName: 'id')]
    private Collection $friends;

    public function __construct()
    {
        $this->playerGame = new ArrayCollection();
        $this->playerCharts = new ArrayCollection();
        $this->friends = new ArrayCollection();
    }

    public function __toString()
    {
        return sprintf('%s (%d)', $this->getPseudo(), $this->getId());
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setPseudo(string $pseudo): void
    {
        $this->pseudo = $pseudo;
    }

    public function getPseudo(): string
    {
        return $this->pseudo;
    }

    public function setAvatar(string $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }

    public function setGamerCard(?string $gamerCard): void
    {
        $this->gamerCard = $gamerCard;
    }

    public function getGamerCard(): ?string
    {
        return $this->gamerCard;
    }


    public function setRankProof(int $rankProof): void
    {
        $this->rankProof = $rankProof;
    }

    public function getRankProof(): ?int
    {
        return $this->rankProof;
    }

    public function setRankCountry(int $rankCountry): void
    {
        $this->rankCountry = $rankCountry;
    }

    public function getRankCountry(): ?int
    {
        return $this->rankCountry;
    }

    public function setNbChartMax(int $nbChartMax): void
    {
        $this->nbChartMax = $nbChartMax;
    }

    public function getNbChartMax(): int
    {
        return $this->nbChartMax;
    }

    public function setNbChartWithPlatform(int $nbChartWithPlatform): void
    {
        $this->nbChartWithPlatform = $nbChartWithPlatform;
    }

    public function getNbChartWithPlatform(): int
    {
        return $this->nbChartWithPlatform;
    }

    public function setNbChartDisabled(int $nbChartDisabled): void
    {
        $this->nbChartDisabled = $nbChartDisabled;
    }

    public function getNbChartDisabled(): int
    {
        return $this->nbChartDisabled;
    }

    public function getLastLogin(): ?DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?DateTime $time = null): void
    {
        $this->lastLogin = $time;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): Player
    {
        $this->user_id = $userId;
        return $this;
    }

    public function setTeam(?Team $team = null): void
    {
        $this->team = $team;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function getLastDisplayLostPosition(): ?DateTime
    {
        return $this->lastDisplayLostPosition;
    }


    public function setLastDisplayLostPosition(?DateTime $lastDisplayLostPosition = null): void
    {
        $this->lastDisplayLostPosition = $lastDisplayLostPosition;
    }

    public function setBoolMaj(bool $boolMaj): void
    {
        $this->boolMaj = $boolMaj;
    }

    public function getBoolMaj(): bool
    {
        return $this->boolMaj;
    }

    public function getHasDonate(): bool
    {
        return $this->hasDonate;
    }

    public function setHasDonate(bool $hasDonate): void
    {
        $this->hasDonate = $hasDonate;
    }

    public function setStatus(PlayerStatusEnum $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): PlayerStatusEnum
    {
        return $this->status;
    }

    /**
     * Get status label for display
     */
    public function getStatusLabel(): string
    {
        return $this->status->getLabel();
    }

    /**
     * Get status French label for display
     */
    public function getStatusFrenchLabel(): string
    {
        return $this->status->getFrenchLabel();
    }

    /**
     * Get status CSS class
     */
    public function getStatusClass(): string
    {
        return $this->status->getClass();
    }

    /**
     * Check if player has admin privileges
     */
    public function isAdmin(): bool
    {
        return $this->status->isAdmin();
    }

    /**
     * Check if player has moderation privileges
     */
    public function isModerator(): bool
    {
        return $this->status->isModerator();
    }

    /**
     * Check if player can manage proofs
     */
    public function canManageProofs(): bool
    {
        return $this->status->canManageProofs();
    }

    /**
     * Check if player can manage games
     */
    public function canManageGames(): bool
    {
        return $this->status->canManageGames();
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return int
     */
    public function getNbConnexion(): int
    {
        return $this->nbConnexion;
    }

    public function setNbConnexion(int $nbConnexion): void
    {
        $this->nbConnexion = $nbConnexion;
    }


    /**
     * @return array<string>
     */
    public function getSluggableFields(): array
    {
        return ['pseudo'];
    }

    public function getInitial(): string
    {
        return substr($this->pseudo, 0, 1);
    }


    public function isLeader(): bool
    {
        return ($this->getTeam() !== null) && ($this->getTeam()->getLeader()->getId() === $this->getId());
    }

    /**
     * @return Collection<int, PlayerChart>
     */
    public function getPlayerCharts(): Collection
    {
        return $this->playerCharts;
    }

    /**
     * @return Collection<int, Player>
     */
    public function getFriends(): Collection
    {
        return $this->friends;
    }

    public function addFriend(Player $friend): self
    {
        if (!$this->friends->contains($friend)) {
            $this->friends->add($friend);
        }

        return $this;
    }

    public function removeFriend(Player $friend): self
    {
        $this->friends->removeElement($friend);

        return $this;
    }

    public function getUrl(): string
    {
        return sprintf(
            '%s-player-p%d/index',
            $this->getSlug(),
            $this->getId()
        );
    }
}
