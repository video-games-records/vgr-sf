<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Presentation\Twig\Components;

use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Proof;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('proof_display', template: '@VideoGamesRecordsProof/components/proof_display.html.twig')]
class ProofDisplay
{
    public ?Proof $proof = null;

    public function isPicture(): bool
    {
        return $this->proof?->getPicture() !== null;
    }

    public function isVideo(): bool
    {
        return $this->proof?->getVideo() !== null;
    }

    public function getPictureId(): ?int
    {
        return $this->proof?->getPicture()?->getId();
    }

    public function getVideoEmbeddedUrl(): ?string
    {
        return $this->proof?->getVideo()?->getEmbeddedUrl();
    }

    public function getVideoTitle(): ?string
    {
        return $this->proof?->getVideo()?->getTitle();
    }
}
