<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\EventSubscriber\Notify\Badge;

use App\BoundedContext\Message\Domain\ValueObject\MessageTypeEnum;
use App\SharedKernel\Infrastructure\EventSubscriber\Notify\AbstractNotifySubscriberInterface;
use Doctrine\ORM\Exception\ORMException;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Event\TeamMasterBadgeLost;

final class NotifyTeamMasterBadgeLostSubscriber extends AbstractNotifySubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            TeamMasterBadgeLost::class => 'notify',
        ];
    }

    /**
     * @param TeamMasterBadgeLost $event
     * @throws ORMException
     */
    public function notify(TeamMasterBadgeLost $event): void
    {
        $teamBadge = $event->getTeamBadge();
        $game = $event->getGame();
        $team = $teamBadge->getTeam();
        $this->messageBuilder
            ->setSender($this->getDefaultSender())
            ->setType(MessageTypeEnum::VGR_TEAM_BADGE);


        // Send MP to team leader
        $this->sendMessage($team->getLeader()->getUserId(), $game);

        // Send MP to players of team
        $query = $this->em->createQueryBuilder()
            ->select('p')
            ->from('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player', 'p')
            ->innerJoin('p.playerGame', 'pg')
            ->where('p.team = :team')
            ->setParameter('team', $team)
            ->andWhere('pg.player != :player')
            ->setParameter('player', $team->getLeader())
            ->andWhere('pg.game = :game')
            ->setParameter('game', $game);

        $players = $query->getQuery()->getResult();
        foreach ($players as $player) {
            $this->sendMessage($player->getId(), $game);
        }
    }

    /**
     * @param $user_id
     * @param $game
     * @return void
     */
    private function sendMessage($user_id, $game): void
    {
        /** @var User $recipient */
        $recipient = $this->em->getRepository('App\BoundedContext\User\Domain\Entity\User')->find($user_id);
        $url = '/' . $recipient->getLanguage() . '/' . $game->getUrl();
        $this->messageBuilder
            ->setObject(
                $this->translator->trans(
                    'team_badge_lost.object',
                    [],
                    'VgrCoreNotification',
                    $recipient->getLanguage()
                )
            )
            ->setMessage(
                sprintf(
                    $this->translator->trans(
                        'team_badge_lost.message',
                        [],
                        'VgrCoreNotification',
                        $recipient->getLanguage()
                    ),
                    $recipient->getUsername(),
                    $url,
                    $game->getName($recipient->getLanguage())
                )
            )
            ->setRecipient($recipient)
            ->send();
    }
}
