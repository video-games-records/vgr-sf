<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Presentation\Twig\Components;

use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Proof;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('player_chart_status')]
class PlayerChartStatus
{
    public ?PlayerChartStatusEnum $status = null;
    public ?Proof $proof = null;

    public function getCssClass(): string
    {
        return $this->status?->getCssClass() ?? 'none';
    }

    public function getLabel(): string
    {
        return $this->status?->getLabel() ?? 'None';
    }
}
