<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;
use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository\BadgeRepository;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\NbPlayerTrait;

#[ORM\Table(name:'vgr_badge')]
#[ORM\Entity(repositoryClass: BadgeRepository::class)]
#[ORM\Index(name: "idx_type", columns: ["type"])]
#[ORM\Index(name: "idx_value", columns: ["value"])]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'dtype', type: 'string')]
#[ORM\DiscriminatorMap([
    'Badge'         => Badge::class,
    'MasterBadge'   => MasterBadge::class,
    'SerieBadge'    => SerieBadge::class,
    'PlatformBadge' => PlatformBadge::class,
    'CountryBadge'  => CountryBadge::class,
])]
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


    public function __toString()
    {
        return sprintf('%s / %s [%s]', $this->getType()->value, $this->getPicture(), $this->getId());
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setType(BadgeType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): BadgeType
    {
        return $this->type;
    }

    public function setPicture(string $picture): static
    {
        $this->picture = $picture;
        return $this;
    }

    public function getPicture(): string
    {
        return $this->picture;
    }

    public function setValue(int $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function majValue(?Game $game = null): void
    {
        // comportement par défaut : rien (surchargé dans MasterBadge)
    }

    public function isTypeMaster(): bool
    {
        return $this instanceof MasterBadge;
    }
}
