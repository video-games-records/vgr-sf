<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\EventSubscriber\Notify\Proof;

use App\BoundedContext\Message\Domain\ValueObject\MessageTypeEnum;
use App\SharedKernel\Infrastructure\EventSubscriber\Notify\AbstractNotifySubscriberInterface;
use Doctrine\ORM\Exception\ORMException;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Event\ProofRefused;

final class NotifyProofRefusedSubscriber extends AbstractNotifySubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ProofRefused::class => 'sendMessage',
        ];
    }

    /**
     * @param ProofRefused $event
     * @throws ORMException
     */
    public function sendMessage(ProofRefused $event): void
    {
        $proof = $event->getProof();
        $playerChart = $proof->getPlayerChart();

        if ($playerChart === null) {
            return;
        }

        $this->messageBuilder
            ->setSender($this->getDefaultSender())
            ->setType(MessageTypeEnum::VGR_PROOF_REFUSED);

        /** @var User $recipient */
        $recipient = $this->em->getRepository('App\BoundedContext\User\Domain\Entity\User')
            ->find($proof->getPlayer()->getUserId());
        $url = '/' . $recipient->getLanguage() . '/' . $playerChart->getUrl();
        $this->messageBuilder
            ->setObject(
                $this->translator->trans(
                    'proof_refused.object',
                    [],
                    'VgrCoreNotification',
                    $recipient->getLanguage()
                )
            )
            ->setMessage(
                sprintf(
                    $this->translator->trans(
                        'proof_refused.message',
                        [],
                        'VgrCoreNotification',
                        $recipient->getLanguage()
                    ),
                    $recipient->getUsername(),
                    $url,
                    $playerChart->getChart()->getCompleteName($recipient->getLanguage()),
                    $proof->getResponse()
                )
            )
            ->setRecipient($recipient)
            ->send();
    }
}
