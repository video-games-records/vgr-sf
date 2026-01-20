<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Presentation\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('player_link')]
class PlayerLink
{
    public object $player;
    public bool $bold = false;
    public bool $showTeam = true;
    public bool $showCountry = true;
    public bool $showAvatar = false;
    public bool $prefetch = false;
    public int $avatarSize = 40;
}
