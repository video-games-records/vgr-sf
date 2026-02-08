<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\EventListener;

use Datetime;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\ProofRequest;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Event\ProofRequestAccepted;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Event\ProofRequestRefused;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\ProofRequestStatus;

#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: ProofRequest::class)]
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: ProofRequest::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: ProofRequest::class)]
class ProofRequestListener
{
    /** @var array<string, array{0: mixed, 1: mixed}|mixed> */
    private array $changeSet = [];
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param ProofRequest $proofRequest
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(ProofRequest $proofRequest, PreUpdateEventArgs $event): void
    {
        $this->changeSet = $event->getEntityChangeSet();
    }

    /**
     * @param ProofRequest $proofRequest
     * @param LifecycleEventArgs $event
     * @phpstan-param LifecycleEventArgs<EntityManagerInterface> $event
     */
    public function postPersist(ProofRequest $proofRequest, LifecycleEventArgs $event): void
    {
        $em = $event->getObjectManager();
        $playerChart = $proofRequest->getPlayerChart();
        $playerChart->setStatus(PlayerChartStatusEnum::REQUEST_PENDING);
        $em->flush();
    }


    /**
     * @param ProofRequest $proofRequest
     * @param LifecycleEventArgs $event
     * @phpstan-param LifecycleEventArgs<EntityManagerInterface> $event
     * @throws ORMException
     */
    public function postUpdate(ProofRequest $proofRequest, LifecycleEventArgs $event): void
    {

        if ($this->isAccepted()) {
            $proofRequest->getPlayerChart()->setStatus(PlayerChartStatusEnum::REQUEST_VALIDATED);

            $proofRequest->setDateAcceptance(new DateTime());
            $this->eventDispatcher->dispatch(new ProofRequestAccepted($proofRequest));
        }

        if ($this->isRefused()) {
            $proofRequest->getPlayerChart()->setStatus(PlayerChartStatusEnum::NONE);

            $proofRequest->setDateAcceptance(new DateTime());
            $this->eventDispatcher->dispatch(new ProofRequestRefused($proofRequest));
        }
    }

    private function isAccepted(): bool
    {
        return array_key_exists('status', $this->changeSet)
           && $this->changeSet['status'][0] === ProofRequestStatus::IN_PROGRESS
           && $this->changeSet['status'][1] === ProofRequestStatus::ACCEPTED;
    }

    private function isRefused(): bool
    {
        return array_key_exists('status', $this->changeSet)
            && $this->changeSet['status'][0] === ProofRequestStatus::IN_PROGRESS
            && $this->changeSet['status'][1] === ProofRequestStatus::REFUSED;
    }
}
