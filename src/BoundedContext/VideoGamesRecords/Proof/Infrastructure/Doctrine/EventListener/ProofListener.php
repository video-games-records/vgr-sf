<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Proof;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\ProofStatus;

#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Proof::class)]
class ProofListener
{
    /**
     * @param Proof $proof
     * @param LifecycleEventArgs $event
     * @phpstan-param LifecycleEventArgs<EntityManagerInterface> $event
     * @throws ORMException
     */
    public function postUpdate(Proof $proof, LifecycleEventArgs $event): void
    {
        if ($proof->getStatus() !== ProofStatus::CLOSED) {
            return;
        }

        $playerChart = $proof->getPlayerChart();
        if ($playerChart === null) {
            return;
        }

        $em = $event->getObjectManager();
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
