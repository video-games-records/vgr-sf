<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\EventSubscriber\Notify\ProofRequest;

use App\BoundedContext\Message\Domain\ValueObject\MessageTypeEnum;
use App\SharedKernel\Infrastructure\EventSubscriber\Notify\AbstractNotifySubscriberInterface;
use Doctrine\ORM\Exception\ORMException;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Event\ProofRequestAccepted;

final class NotifyProofRequestAcceptedSubscriber extends AbstractNotifySubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ProofRequestAccepted::class => 'sendMessage',
        ];
    }

    /**
     * @param ProofRequestAccepted $event
     * @throws ORMException
     */
    public function sendMessage(ProofRequestAccepted $event): void
    {
        $proofRequest = $event->getProofRequest();
        $this->messageBuilder
            ->setSender($this->getDefaultSender())
            ->setType(MessageTypeEnum::VGR_PROOF_REQUEST_ACCEPTED);

        // Send MP (1)
        /** @var User $recipient */
        $recipient = $this->em->getRepository('App\BoundedContext\User\Domain\Entity\User')
            ->find($proofRequest->getPlayerChart()->getPlayer()->getUserId());
        $url = '/' . $recipient->getLanguage() . '/' . $proofRequest->getPlayerChart()->getUrl();
        $this->messageBuilder
            ->setObject(
                $this->translator->trans(
                    'proof_request_confirm.object',
                    [],
                    'VgrCoreNotification',
                    $recipient->getLanguage()
                )
            )
            ->setMessage(
                sprintf(
                    $this->translator->trans(
                        'proof_request_confirm.message',
                        [],
                        'VgrCoreNotification',
                        $recipient->getLanguage()
                    ),
                    $recipient->getUsername(),
                    $url,
                    $proofRequest->getPlayerChart()->getChart()->getCompleteName($recipient->getLanguage())
                )
            )
            ->setRecipient($recipient)
            ->send();

        // Send MP (2)
        /** @var User $recipient */
        $recipient = $this->em->getRepository('App\BoundedContext\User\Domain\Entity\User')
            ->find($proofRequest->getPlayerRequesting()->getUserId());
        $this->messageBuilder
            ->setObject(
                $this->translator->trans(
                    'proof_request_accepted.object',
                    [],
                    'VgrCoreNotification',
                    $recipient->getLanguage()
                )
            )
            ->setMessage(
                sprintf(
                    $this->translator->trans(
                        'proof_request_accepted.message',
                        [],
                        'VgrCoreNotification',
                        $recipient->getLanguage()
                    ),
                    $recipient->getUsername(),
                    $url,
                    $proofRequest->getPlayerChart()->getChart()->getCompleteName($recipient->getLanguage()),
                    $proofRequest->getPlayerChart()->getPlayer()->getPseudo(),
                    $proofRequest->getResponse()
                )
            )
            ->setRecipient($recipient)
            ->send();
    }
}
