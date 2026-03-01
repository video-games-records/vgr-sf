<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Presentation\Twig\Extension;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\Badge;
use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository\PlayerBadgeRepository;
use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository\TeamBadgeRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BadgeHistoryExtension extends AbstractExtension
{
    public function __construct(
        private readonly PlayerBadgeRepository $playerBadgeRepository,
        private readonly TeamBadgeRepository $teamBadgeRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('vgr_player_badge_history', [$this, 'getPlayerBadgeHistory']),
            new TwigFunction('vgr_team_badge_history', [$this, 'getTeamBadgeHistory']),
        ];
    }

    /**
     * @return array<array{pseudo: string, createdAt: \DateTime, endedAt: \DateTime|null, mbOrder: int|null}>
     */
    public function getPlayerBadgeHistory(Badge $badge): array
    {
        return $this->playerBadgeRepository->getHistoryForBadge($badge);
    }

    /**
     * @return array<array{libTeam: string, createdAt: \DateTime, endedAt: \DateTime|null, mbOrder: int|null}>
     */
    public function getTeamBadgeHistory(Badge $badge): array
    {
        return $this->teamBadgeRepository->getHistoryForBadge($badge);
    }
}
