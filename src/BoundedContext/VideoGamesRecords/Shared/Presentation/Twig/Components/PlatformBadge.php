<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Presentation\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('platform_badge')]
class PlatformBadge
{
    public object $platform;
}
