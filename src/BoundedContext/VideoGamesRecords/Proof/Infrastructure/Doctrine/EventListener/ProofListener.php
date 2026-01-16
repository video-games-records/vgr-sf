<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\EventListener;

use DateTime;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Proof;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Event\ProofAccepted;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Event\ProofRefused;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\ProofStatus;

#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Proof::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Proof::class)]
class ProofListener
{
    /** @var array<string, array{0: mixed, 1: mixed}> */
    private array $changeSet = [];
    private UserProvider $userProvider;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(UserProvider $userProvider, EventDispatcherInterface $eventDispatcher)
    {
        $this->userProvider = $userProvider;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Proof              $proof
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Proof $proof, PreUpdateEventArgs $event): void
    {
        $this->changeSet = $event->getEntityChangeSet();
    }

    /**
     * @param Proof $proof
     * @param LifecycleEventArgs $event
     * @throws ORMException
     */
    public function postUpdate(Proof $proof, LifecycleEventArgs $event): void
    {
        $em = $event->getObjectManager();
        $event = new ProofAccepted($proof);

        // ACCEPTED
        if ($this->isAccepted()) {
            $proof->getPlayerChart()->setStatus(PlayerChartStatusEnum::PROVED);

            $proof->setPlayerResponding($this->userProvider->getPlayer());
            $proof->setCheckedAt(new DateTime());
            $this->eventDispatcher->dispatch(new ProofAccepted($proof));
        }

        // REFUSED
        if ($this->isRefused()) {
            $playerChart = $proof->getPlayerChart();
            if ($playerChart->getStatus() === PlayerChartStatusEnum::PROVED) {
                $playerChart->setStatus(PlayerChartStatusEnum::NONE);
            } else {
                $status = ($playerChart->getStatus() === PlayerChartStatusEnum::PROOF_SENT)
                   ? PlayerChartStatusEnum::NONE : PlayerChartStatusEnum::REQUEST_VALIDATED;
                $playerChart->setStatus($status);
            }

            $proof->setPlayerResponding($this->userProvider->getPlayer());
            $proof->setCheckedAt(new DateTime());
            $this->eventDispatcher->dispatch(new ProofRefused($proof));
        }

        // CLOSED
        if ($proof->getStatus()->getValue() == ProofStatus::CLOSED) {
            $playerChart = $proof->getPlayerChart();
            if ($playerChart) {
                $playerChart->setProof(null);
                switch ($playerChart->getStatus()) {
                    case PlayerChartStatusEnum::REQUEST_VALIDATED:
                    case PlayerChartStatusEnum::REQUEST_PROOF_SENT:
                        $playerChart->setStatus(PlayerChartStatusEnum::REQUEST_VALIDATED);
                        break;
                    case PlayerChartStatusEnum::PROVED:
                    case PlayerChartStatusEnum::PROOF_SENT:
                        $playerChart->setStatus(PlayerChartStatusEnum::NONE);
                        break;
                }
                $em->flush();
            }
        }
    }


    private function isAccepted(): bool
    {
        return array_key_exists('status', $this->changeSet)
            && $this->changeSet['status'][0] === ProofStatus::IN_PROGRESS
            && $this->changeSet['status'][1] === ProofStatus::ACCEPTED;
    }

    private function isRefused(): bool
    {
        return array_key_exists('status', $this->changeSet)
            && in_array(
                $this->changeSet['status'][0],
                [ProofStatus::IN_PROGRESS, ProofStatus::ACCEPTED]
            )
            && $this->changeSet['status'][1] === ProofStatus::REFUSED;
    }
}
