<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\DiscordRepository;

#[ORM\Table(name:'vgr_discord')]
#[ORM\Entity(repositoryClass: DiscordRepository::class)]
class Discord
{
    use TimestampableEntity;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: false)]
    private string $name;

    #[Assert\NotBlank]
    #[Assert\Url]
    #[Assert\Length(max: 500)]
    #[ORM\Column(length: 500, nullable: false)]
    private string $url;

    /**
     * @var Collection<int, Game>
     */
    #[ORM\ManyToMany(targetEntity: Game::class, inversedBy: 'discords')]
    #[ORM\JoinTable(name: 'vgr_game_discord')]
    private Collection $games;

    public function __construct()
    {
        $this->games = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('Discord [%s]', $this->name ?? $this->id);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return Collection<int, Game>
     */
    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): void
    {
        if (!$this->games->contains($game)) {
            $this->games->add($game);
            $game->addDiscord($this);
        }
    }

    public function removeGame(Game $game): void
    {
        if ($this->games->removeElement($game)) {
            $game->removeDiscord($this);
        }
    }
}
