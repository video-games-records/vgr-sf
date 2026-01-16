<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\Repository\VideoRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\DescriptionTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\IsActiveTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\LikeCountTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\Player\PlayerTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ThumbnailTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\TitleTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\ViewCountTrait;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\VideoType;

#[ORM\Table(name:'vgr_video')]
#[ORM\Entity(repositoryClass: VideoRepository::class)]
#[ORM\UniqueConstraint(name: "unq_video", columns: ["type", "external_id"])]
#[DoctrineAssert\UniqueEntity(fields: ['url'])]
#[DoctrineAssert\UniqueEntity(fields: ['type', 'externalId'])]
class Video
{
    use TimestampableEntity;
    use PlayerTrait;
    use ViewCountTrait;
    use LikeCountTrait;
    use TitleTrait;
    use DescriptionTrait;
    use ThumbnailTrait;
    use IsActiveTrait;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\Length(max: 50)]
    #[ORM\Column(length: 50, nullable: false)]
    private string $type = VideoType::YOUTUBE;

    #[ORM\Column(length: 50, nullable: false)]
    private string $externalId;

    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 255)]
    #[ORM\Column(length: 255, nullable: false)]
    private string $url;


    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $nbComment = 0;

    #[ORM\Column(length: 255)]
    #[Gedmo\Slug(fields: ['title'])]
    protected string $slug;


    #[ORM\ManyToOne(targetEntity: Game::class)]
    #[ORM\JoinColumn(name:'game_id', referencedColumnName:'id', nullable:true, onDelete: 'SET NULL')]
    private ?Game $game = null;

    /**
     * @var Collection<int, VideoComment>
     */
    #[ORM\OneToMany(targetEntity: VideoComment::class, mappedBy: 'video')]
    private Collection $comments;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class)]
    #[ORM\JoinTable(name: 'vgr_video_tag')]
    private Collection $tags;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function __toString()
    {
        return sprintf('Video [%s]', $this->id);
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getVideoType(): VideoType
    {
        return new VideoType($this->type);
    }

    public function setExternalId(string $externalId): void
    {
        $this->externalId = $externalId;
    }


    public function getExternalId(): string
    {
        return $this->externalId;
    }


    public function setUrl(string $url): void
    {
        $this->url = $url;
        $this->majTypeAndVideoId();
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setGame(?Game $game = null): void
    {
        $this->game = $game;
    }

    public function setNbComment(int $nbComment): void
    {
        $this->nbComment = $nbComment;
    }

    public function getNbComment(): int
    {
        return $this->nbComment;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    /** @return Collection<int, VideoComment> */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /** @return Collection<int, Tag> */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);
        return $this;
    }

    /**
     *
     */
    public function majTypeAndVideoId(): void
    {
        if (strpos($this->getUrl(), 'youtube')) {
            $this->setType(VideoType::YOUTUBE);
            $explode = explode('=', $this->getUrl());
            $this->setExternalId($explode[1]);
        } elseif (strpos($this->getUrl(), 'youtu.be')) {
            $this->setType(VideoType::YOUTUBE);
            $this->setExternalId(
                substr(
                    $this->getUrl(),
                    strripos($this->getUrl(), '/') + 1,
                    strlen($this->getUrl()) - 1
                )
            );
        } elseif (strpos($this->getUrl(), 'twitch')) {
            $this->setType(VideoType::TWITCH);
            $explode = explode('/', $this->getUrl());
            $this->setExternalId($explode[count($explode) - 1]);
        } else {
            $this->setType(VideoType::UNKNOWN);
        }
    }

    public function getEmbeddedUrl(): string
    {
        if ($this->getVideoType()->getValue() == VideoType::YOUTUBE) {
            return 'https://www.youtube.com/embed/' . $this->getExternalid();
        } elseif ($this->getVideoType()->getValue() == VideoType::TWITCH) {
            return 'https://player.twitch.tv/?autoplay=false&video=v' . $this->getExternalId(
            ) . '&parent=' . $_SERVER['SERVER_NAME'];
        } else {
            return $this->getUrl();
        }
    }

    /**
     * @return array<string>
     */
    public function getSluggableFields(): array
    {
        return ['title'];
    }
}
