<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\EventSubscriber\Notify\Proof;

use App\BoundedContext\Message\Domain\ValueObject\MessageTypeEnum;
use App\SharedKernel\Infrastructure\EventSubscriber\Notify\AbstractNotifySubscriberInterface;
use Doctrine\ORM\Exception\ORMException;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Event\ProofAccepted;

final class NotifyProofAcceptedSubscriber extends AbstractNotifySubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ProofAccepted::class => 'sendMessage',
        ];
    }

    /**
     * @param ProofAccepted $event
     * @throws ORMException
     */
    public function sendMessage(ProofAccepted $event): void
    {
        $proof = $event->getProof();
        $this->messageBuilder
            ->setSender($this->getDefaultSender())
            ->setType(MessageTypeEnum::VGR_PROOF_ACCEPTED);

        /** @var User $recipient */
        $recipient = $this->em->getRepository('App\BoundedContext\User\Domain\Entity\User')
            ->find($proof->getPlayerChart()->getPlayer()->getUserId());
        $url = '/' . $recipient->getLanguage() . '/' . $proof->getPlayerChart()->getUrl();
        $this->messageBuilder
            ->setObject(
                $this->translator->trans(
                    'proof_accepted.object',
                    [],
                    'VgrCoreNotification',
                    $recipient->getLanguage()
                )
            )
            ->setMessage(
                sprintf(
                    $this->translator->trans(
                        'proof_accepted.message',
                        [],
                        'VgrCoreNotification',
                        $recipient->getLanguage()
                    ),
                    $recipient->getUsername(),
                    $url,
                    $proof->getPlayerChart()->getChart()->getCompleteName($recipient->getLanguage()),
                    $proof->getResponse()
                )
            )
            ->setRecipient($recipient)
            ->send();
    }
}
