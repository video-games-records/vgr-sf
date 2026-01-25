<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\EventSubscriber\Notify\PlayerChart;

use App\BoundedContext\Message\Domain\ValueObject\MessageTypeEnum;
use App\SharedKernel\Infrastructure\EventSubscriber\Notify\AbstractNotifySubscriberInterface;
use Doctrine\ORM\Exception\ORMException;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Event\Admin\AdminPlayerChartUpdated;

final class NotifyPlayerChartUpdatedSubscriber extends AbstractNotifySubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            AdminPlayerChartUpdated::class => 'sendMessage',
        ];
    }

    /**
     * @param AdminPlayerChartUpdated $event
     * @throws ORMException
     */
    public function sendMessage(AdminPlayerChartUpdated $event): void
    {
        $playerChart = $event->getPlayerChart();
        $this->messageBuilder
            ->setSender($this->getDefaultSender())
            ->setType(MessageTypeEnum::DEFAULT);

        // Send MP
        /** @var User $recipient */
        $recipient = $this->em->getRepository('App\BoundedContext\User\Domain\Entity\User')
            ->find($playerChart->getPlayer()->getUserId());
        $url = '/' . $recipient->getLanguage() . '/' . $playerChart->getUrl();
        $this->messageBuilder
            ->setObject(
                $this->translator->trans(
                    'player_chart_updated.object',
                    [],
                    'VgrCoreNotification',
                    $recipient->getLanguage()
                )
            )
            ->setMessage(
                sprintf(
                    $this->translator->trans(
                        'player_chart_updated.message',
                        [],
                        'VgrCoreNotification',
                        $recipient->getLanguage()
                    ),
                    $recipient->getUsername(),
                    $url,
                    $playerChart->getChart()->getCompleteName($recipient->getLanguage())
                )
            )
            ->setRecipient($recipient)
            ->send();
    }
}
