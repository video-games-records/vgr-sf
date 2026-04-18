<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameDownloadUrlRepository;

#[ORM\Table(name:'vgr_game_download_url')]
#[ORM\Entity(repositoryClass: GameDownloadUrlRepository::class)]
#[ORM\Index(name: "idx_game_platform", columns: ["game_id", "platform_id"])]
#[ORM\UniqueConstraint(name: "unique_game_platform", columns: ["game_id", "platform_id"])]
class GameDownloadUrl
{
    use TimestampableEntity;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'downloadUrls')]
    #[ORM\JoinColumn(name:'game_id', referencedColumnName:'id', nullable:false)]
    private Game $game;

    #[ORM\ManyToOne(targetEntity: Platform::class)]
    #[ORM\JoinColumn(name:'platform_id', referencedColumnName:'id', nullable:false)]
    private Platform $platform;

    #[Assert\NotBlank]
    #[Assert\Url]
    #[Assert\Length(max: 500)]
    #[ORM\Column(length: 500, nullable: false)]
    private string $url = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setGame(Game $game): static
    {
        $this->game = $game;
        return $this;
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function setPlatform(Platform $platform): static
    {
        $this->platform = $platform;
        return $this;
    }

    public function getPlatform(): Platform
    {
        return $this->platform;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->platform->getName(), $this->url);
    }
}
