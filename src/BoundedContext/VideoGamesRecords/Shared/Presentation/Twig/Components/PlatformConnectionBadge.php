<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Presentation\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('platform_connection_badge')]
class PlatformConnectionBadge
{
    public object $connection;
}
