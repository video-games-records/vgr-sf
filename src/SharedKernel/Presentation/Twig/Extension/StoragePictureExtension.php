<?php

declare(strict_types=1);

namespace App\SharedKernel\Presentation\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class StoragePictureExtension extends AbstractExtension
{
    public function __construct(private readonly string $storagePublicUrl)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('game_picture_url', $this->gamePictureUrl(...)),
            new TwigFunction('serie_picture_url', $this->seriePictureUrl(...)),
            new TwigFunction('badge_picture_url', $this->badgePictureUrl(...)),
        ];
    }

    public function gamePictureUrl(?string $picture): string
    {
        return $this->storagePublicUrl . '/game/' . ($picture ?: 'default.png');
    }

    public function seriePictureUrl(?string $picture): string
    {
        return $this->storagePublicUrl . '/series/' . ($picture ?: 'default.png');
    }

    public function badgePictureUrl(string $directory, string $picture): string
    {
        return $this->storagePublicUrl . '/' . $directory . '/' . $picture;
    }
}
