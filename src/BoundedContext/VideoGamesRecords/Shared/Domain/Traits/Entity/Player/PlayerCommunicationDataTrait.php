<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Domain\Traits\Entity\Player;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait PlayerCommunicationDataTrait
{
    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $website = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $youtube = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $twitch = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $discord = null;

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website = null): static
    {
        $this->website = $website;
        return $this;
    }

    public function getYoutube(): ?string
    {
        return $this->youtube;
    }

    public function setYoutube(?string $youtube = null): static
    {
        $this->youtube = $youtube;
        return $this;
    }

    public function getTwitch(): ?string
    {
        return $this->twitch;
    }

    public function setTwitch(?string $twitch = null): static
    {
        $this->twitch = $twitch;
        return $this;
    }

    public function getDiscord(): ?string
    {
        return $this->discord;
    }

    public function setDiscord(?string $discord): static
    {
        $this->discord = $discord;
        return $this;
    }
}
