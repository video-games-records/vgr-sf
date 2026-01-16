<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;
use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository\BadgeRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbPlayerTrait;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Country;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;

#[ORM\Table(name:'vgr_badge')]
#[ORM\Entity(repositoryClass: BadgeRepository::class)]
#[ORM\Index(name: "idx_type", columns: ["type"])]
#[ORM\Index(name: "idx_value", columns: ["value"])]
class Badge
{
    use NbPlayerTrait;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(enumType: BadgeType::class)]
    private BadgeType $type;

    #[Assert\Length(max: 100)]
    #[ORM\Column(length: 100, nullable: false, options: ['default' => 'default.gif'])]
    private string $picture;

    #[ORM\Column(length: 100, nullable: false, options: ['default' => 0])]
    private int $value = 0;

    #[ORM\OneToOne(targetEntity: Game::class, mappedBy: "badge")]
    private ?Game $game;

    #[ORM\OneToOne(targetEntity: Serie::class, mappedBy: "badge")]
    private ?Serie $serie;

    #[ORM\OneToOne(targetEntity: Country::class, mappedBy: "badge")]
    private ?Country $country;

    #[ORM\OneToOne(targetEntity: Platform::class, mappedBy: "badge")]
    private ?Platform $platform;


    public function __toString()
    {
        return sprintf('%s / %s [%s]', $this->getType()->value, $this->getPicture(), $this->getId());
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setType(BadgeType $type): void
    {
        $this->type = $type;
    }

    public function getType(): BadgeType
    {
        return $this->type;
    }

    public function setPicture(string $picture): void
    {
        $this->picture = $picture;
    }

    public function getPicture(): string
    {
        return $this->picture;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setGame(?Game $game): void
    {
        $this->game = $game;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setSerie(?Serie $serie): void
    {
        $this->serie = $serie;
    }

    public function getSerie(): ?Serie
    {
        return $this->serie;
    }

    public function setCountry(?Country $country): void
    {
        $this->country = $country;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setPlatform(?Platform $platform): void
    {
        $this->platform = $platform;
    }

    public function getPlatform(): ?Platform
    {
        return $this->platform;
    }


    public function majValue(): void
    {
        if (BadgeType::MASTER !== $this->type) {
            return;
        }

        $game = $this->getGame();
        if ($game === null) {
            $this->value = 0;
            return;
        }

        if (0 === $this->getNbPlayer()) {
            $this->value = 0;
        } else {
            $nbPlayerDiff = 100 + $game->getNbPlayer() - $this->nbPlayer;
            $factor = 6250 * (-1 / $nbPlayerDiff + 0.0102);
            $divisor = pow($this->nbPlayer, 1 / 3);
            $this->value = (int) floor(100 * $factor / $divisor);
        }
    }

    public function isTypeMaster(): bool
    {
        return $this->type === BadgeType::MASTER;
    }
}
