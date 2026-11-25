<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerGameRepository;
use App\SharedKernel\Application\Service\GroupMessageBroadcastSender;
use App\SharedKernel\Domain\ValueObject\GroupMessageBroadcastTypeEnum;

readonly class SendGameGroupMessageService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PlayerGameRepository $playerGameRepository,
        private GroupMessageBroadcastSender $groupMessageBroadcastSender,
    ) {
    }

    public function sendToPlayersOfGame(Game $game, User $sender, string $subject, string $message): int
    {
        $players = $this->playerGameRepository->findPlayersByGame($game);

        $userIds = array_values(array_unique(array_map(
            static fn (Player $player): int => $player->getUserId(),
            $players
        )));

        $recipients = [];
        if (!empty($userIds)) {
            $recipients = $this->em->getRepository(User::class)->findBy(['id' => $userIds]);
        }

        return $this->groupMessageBroadcastSender->send(
            GroupMessageBroadcastTypeEnum::GAME,
            (int) $game->getId(),
            $sender,
            $subject,
            $message,
            $recipients
        );
    }
}
