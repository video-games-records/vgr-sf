<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Twig\Extension;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\SharedKernel\Domain\ValueObject\GroupMessageBroadcastTypeEnum;
use App\SharedKernel\Infrastructure\Doctrine\Repository\GroupMessageBroadcastRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GameMessageBroadcastHistoryExtension extends AbstractExtension
{
    public function __construct(
        private readonly GroupMessageBroadcastRepository $groupMessageBroadcastRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('vgr_game_message_broadcast_history', [$this, 'getHistory']),
        ];
    }

    /**
     * @return array<array{id: int|null, subject: string, recipientCount: int, createdAt: \DateTime|null, senderUsername: string}>
     */
    public function getHistory(Game $game): array
    {
        return $this->groupMessageBroadcastRepository->getHistoryForGroup(
            GroupMessageBroadcastTypeEnum::GAME,
            (int) $game->getId()
        );
    }
}
