<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\Repository\PictureRepository;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\Game\GameTrait;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\Player\PlayerTrait;

#[ORM\Table(name:'vgr_picture')]
#[ORM\Entity(repositoryClass: PictureRepository::class)]
class Picture
{
    use PlayerTrait;
    use GameTrait;
    use TimestampableEntity;

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\NotNull]
    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: false)]
    private string $path = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $metadata = null;

    #[Assert\NotNull]
    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: false)]
    private string $hash;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setMetadata(string $metadata): static
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function getMetadata(): ?string
    {
        return $this->metadata;
    }

    public function setHash(string $hash): static
    {
        $this->hash = $hash;
        return $this;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function __toString()
    {
        return sprintf('Picture [%s]', $this->id);
    }
}
