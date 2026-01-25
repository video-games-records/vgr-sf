<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\EventSubscriber\Notify\Badge;

use App\BoundedContext\Message\Domain\ValueObject\MessageTypeEnum;
use App\SharedKernel\Infrastructure\EventSubscriber\Notify\AbstractNotifySubscriberInterface;
use Doctrine\ORM\Exception\ORMException;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Event\PlayerMasterBadgeLost;

final class NotifyPlayerMasterBadgeLostSubscriber extends AbstractNotifySubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            PlayerMasterBadgeLost::class => 'sendMessage',
        ];
    }

    /**
     * @param PlayerMasterBadgeLost $event
     * @throws ORMException
     */
    public function sendMessage(PlayerMasterBadgeLost $event): void
    {
        $playerBadge = $event->getPlayerBadge();
        $game = $event->getGame();
        $this->messageBuilder
            ->setSender($this->getDefaultSender())
            ->setType(MessageTypeEnum::VGR_PLAYER_BADGE);


        // Send MP
        /** @var User $recipient */
        $recipient = $this->em->getRepository('App\BoundedContext\User\Domain\Entity\User')
            ->find($playerBadge->getPlayer()->getUserId());
        $url = '/' . $recipient->getLanguage() . '/' . $game->getUrl();
        $this->messageBuilder
            ->setObject(
                $this->translator->trans(
                    'player_badge_lost.object',
                    [],
                    'VgrCoreNotification',
                    $recipient->getLanguage()
                )
            )
            ->setMessage(
                sprintf(
                    $this->translator->trans(
                        'player_badge_lost.message',
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
