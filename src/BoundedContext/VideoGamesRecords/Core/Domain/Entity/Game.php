<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Intl\Locale;
use Symfony\Component\Validator\Constraints as Assert;
use App\BoundedContext\Forum\Domain\Entity\Forum;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\IsRankTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\LastUpdateTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbChartTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbPlayerTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbPostTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbTeamTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbVideoTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\PictureTrait;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\GameStatus;
use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\Game as IgdbGame;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\Badge;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamGame;
use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Contracts\GameInfoInterface;

#[ORM\Table(name:'vgr_game')]
#[ORM\Entity(repositoryClass: GameRepository::class)]
#[ORM\Index(name: "idx_lib_game_fr", columns: ["lib_game_fr"])]
#[ORM\Index(name: "idx_lib_game_en", columns: ["lib_game_en"])]
#[ORM\Index(name: "status", columns: ["status"])]
class Game implements GameInfoInterface
{
    use TimestampableEntity;
    use NbChartTrait;
    use NbPostTrait;
    use NbPlayerTrait;
    use NbTeamTrait;
    use PictureTrait;
    use NbVideoTrait;
    use IsRankTrait;
    use LastUpdateTrait;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    protected ?int $id = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: false)]
    private string $libGameEn = '';

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: false)]
    private string $libGameFr = '';

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $downloadUrl;

    #[ORM\Column(length: 30, nullable: false, options: ['default' => GameStatus::CREATED])]
    private string $status = GameStatus::CREATED;

    #[ORM\Column(nullable: true)]
    private ?DateTime $publishedAt = null;

    #[ORM\ManyToOne(targetEntity: IgdbGame::class)]
    #[ORM\JoinColumn(name:'igdb_game_id', referencedColumnName:'id', nullable:true)]
    private ?IgdbGame $igdbGame = null;

    #[ORM\ManyToOne(targetEntity: Serie::class, inversedBy: 'games')]
    #[ORM\JoinColumn(name:'serie_id', referencedColumnName:'id', nullable:true)]
    private ?Serie $serie = null;

    #[ORM\OneToOne(targetEntity: Badge::class, cascade: ['persist'], inversedBy: 'game')]
    #[ORM\JoinColumn(name:'badge_id', referencedColumnName:'id', nullable:false)]
    private Badge $badge;

    /**
     * @var Collection<int, Group>
     */
    #[ORM\OneToMany(targetEntity: Group::class, cascade:['persist', 'remove'], mappedBy: 'game', orphanRemoval: true)]
    private Collection $groups;

    /**
     * @var Collection<int, Platform>
     */
    #[ORM\JoinTable(name: 'vgr_game_platform')]
    #[ORM\JoinColumn(name: 'game_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'platform_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Platform::class, inversedBy: 'games')]
    private Collection $platforms;

    #[ORM\OneToOne(targetEntity: Forum::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name:'forum_id', referencedColumnName:'id', nullable:false)]
    private Forum $forum;

    #[ORM\OneToOne(targetEntity: PlayerChart::class)]
    #[ORM\JoinColumn(name:'last_score_id', referencedColumnName:'id', nullable:true)]
    private ?PlayerChart $lastScore;

    #[ORM\Column(length: 255)]
    #[Gedmo\Slug(fields: ['libGameEn'])]
    protected string $slug;

    /**
     * @var Collection<int, Rule>
     */
    #[ORM\ManyToMany(targetEntity: Rule::class, inversedBy: 'games')]
    #[ORM\JoinTable(name: 'vgr_rule_game')]
    private Collection $rules;

    /**
     * @var Collection<int, PlayerGame>
     */
    #[ORM\OneToMany(targetEntity: PlayerGame::class, mappedBy: 'game')]
    private Collection $playerGame;

    /**
     * @var Collection<int, TeamGame>
     */
    #[ORM\OneToMany(targetEntity: TeamGame::class, mappedBy: 'game')]
    private Collection $teamGame;

    /**
     * @var Collection<int, Discord>
     */
    #[ORM\ManyToMany(targetEntity: Discord::class, mappedBy: 'games')]
    private Collection $discords;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->platforms = new ArrayCollection();
        $this->rules = new ArrayCollection();
        $this->playerGame = new ArrayCollection();
        $this->teamGame = new ArrayCollection();
        $this->discords = new ArrayCollection();
    }

    public function __toString()
    {
        return sprintf('%s (%d)', $this->getName(), $this->getId());
    }

    public function getDefaultName(): string
    {
        return $this->libGameEn;
    }

    public function getName(?string $locale = null): string
    {
        if ($locale === null) {
            $locale = Locale::getDefault();
        }
        if ($locale == 'fr') {
            return $this->libGameFr;
        } else {
            return $this->libGameEn;
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

    public function setLibGameEn(string $libGameEn): void
    {
        $this->libGameEn = $libGameEn;
    }

    public function getLibGameEn(): string
    {
        return $this->libGameEn;
    }

    public function setLibGameFr(?string $libGameFr): void
    {
        if ($libGameFr) {
            $this->libGameFr = $libGameFr;
        }
    }

    public function getLibGameFr(): string
    {
        return $this->libGameFr;
    }

    public function setDownloadurl(?string $downloadUrl = null): void
    {
        $this->downloadUrl = $downloadUrl;
    }

    public function getDownloadUrl(): ?string
    {
        return $this->downloadUrl;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getGameStatus(): GameStatus
    {
        return new GameStatus($this->status);
    }

    public function getStatusAsString(): string
    {
        return $this->status;
    }

    public function setPublishedAt(?DateTime $pubishedAt = null): void
    {
        $this->publishedAt = $pubishedAt;
    }

    public function getPublishedAt(): ?DateTime
    {
        return $this->publishedAt;
    }

    public function setIgdbGame(?IgdbGame $igdbGame): void
    {
        $this->igdbGame = $igdbGame;
    }

    public function getIgdbGame(): ?IgdbGame
    {
        return $this->igdbGame;
    }

    public function getIgdbId(): ?int
    {
        return $this->igdbGame?->getId();
    }

    public function setSerie(?Serie $serie = null): void
    {
        $this->serie = $serie;
    }

    public function getSerie(): ?Serie
    {
        return $this->serie;
    }

    public function setBadge(Badge $badge): void
    {
        $this->badge = $badge;
    }

    public function getBadge(): Badge
    {
        return $this->badge;
    }

    public function addGroup(Group $group): void
    {
        $group->setGame($this);
        $this->groups[] = $group;
    }

    public function removeGroup(Group $group): void
    {
        $this->groups->removeElement($group);
    }

    /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addPlatform(Platform $platform): void
    {
        $this->platforms[] = $platform;
    }

    public function removePlatform(Platform $platform): void
    {
        $this->platforms->removeElement($platform);
    }

    /**
     * @return Collection<int, Platform>
     */
    public function getPlatforms(): Collection
    {
        return $this->platforms;
    }

    public function getForum(): Forum
    {
        return $this->forum;
    }

    public function setForum(Forum $forum): void
    {
        $this->forum = $forum;
    }

    public function getLastScore(): ?PlayerChart
    {
        return $this->lastScore;
    }

    public function setLastScore(?PlayerChart $lastScore): void
    {
        $this->lastScore = $lastScore;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getUrl(): string
    {
        return sprintf(
            '%s-game-g%d/index',
            $this->getSlug(),
            $this->getId()
        );
    }

    public function addRule(Rule $rule): void
    {
        $this->rules[] = $rule;
    }

    public function removeRule(Rule $rule): void
    {
        $this->rules->removeElement($rule);
    }

    /**
     * @return Collection<int, PlayerGame>
     */
    public function getPlayerGame(): Collection
    {
        return $this->playerGame;
    }

    /**
     * @return Collection<int, TeamGame>
     */
    public function getTeamGame(): Collection
    {
        return $this->teamGame;
    }

    /**
     * @return Collection<int, Rule>
     */
    public function getRules(): Collection
    {
        return $this->rules;
    }

    /**
     * @return Collection<int, Discord>
     */
    public function getDiscords(): Collection
    {
        return $this->discords;
    }

    public function addDiscord(Discord $discord): void
    {
        if (!$this->discords->contains($discord)) {
            $this->discords->add($discord);
            $discord->addGame($this);
        }
    }

    public function removeDiscord(Discord $discord): void
    {
        if ($this->discords->removeElement($discord)) {
            $discord->removeGame($this);
        }
    }

    public function getGenres(): Collection
    {
        return $this->igdbGame?->getGenres() ?? new ArrayCollection();
    }

    public function getReleaseDate(): ?DateTime
    {
        $timestamp = $this->igdbGame?->getFirstReleaseDate();
        if ($timestamp === null) {
            return null;
        }

        try {
            return new DateTime('@' . $timestamp);
        } catch (\Exception) {
            return null;
        }
    }

    public function getSummary(): ?string
    {
        return $this->igdbGame?->getSummary();
    }

    public function getStoryline(): ?string
    {
        return $this->igdbGame?->getStoryline();
    }
}
