<?php

namespace App\SharedKernel\Infrastructure\EventSubscriber\Notify\ProofRequest;

use App\BoundedContext\Message\Domain\ValueObject\MessageTypeEnum;
use App\SharedKernel\Infrastructure\EventSubscriber\Notify\AbstractNotifySubscriberInterface;
use Doctrine\ORM\Exception\ORMException;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Event\ProofRequestRefused;

final class NotifyProofRequestRefusedSubscriber extends AbstractNotifySubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ProofRequestRefused::class => 'sendMessage',
        ];
    }

    /**
     * @param ProofRequestRefused $event
     * @throws ORMException
     */
    public function sendMessage(ProofRequestRefused $event): void
    {
        $proofRequest = $event->getProofRequest();
        $this->messageBuilder
            ->setSender($this->getDefaultSender())
            ->setType(MessageTypeEnum::VGR_PROOF_REQUEST_REFUSED);

        /** @var User $recipient */
        $recipient = $this->em->getRepository('App\BoundedContext\User\Domain\Entity\User')
            ->find($proofRequest->getPlayerRequesting()->getUserId());
        $url = '/' . $recipient->getLanguage() . '/' . $proofRequest->getPlayerChart()->getUrl();
        $this->messageBuilder
            ->setObject(
                $this->translator->trans(
                    'proof_request_refused.object',
                    [],
                    'VgrCoreNotification',
                    $recipient->getLanguage()
                )
            )
            ->setMessage(
                sprintf(
                    $this->translator->trans(
                        'proof_request_refused.message',
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
