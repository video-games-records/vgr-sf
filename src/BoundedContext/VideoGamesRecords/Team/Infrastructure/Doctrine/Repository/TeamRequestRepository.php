<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamRequest;
use App\BoundedContext\VideoGamesRecords\Team\Domain\ValueObject\TeamRequestStatus;
use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends DefaultRepository<TeamRequest>
 */
class TeamRequestRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamRequest::class);
    }

    /**
     * @return TeamRequest[]
     */
    public function findActiveByTeam(Team $team): array
    {
        return $this->createQueryBuilder('tr')
            ->where('tr.team = :team')
            ->andWhere('tr.status = :status')
            ->setParameter('team', $team)
            ->setParameter('status', TeamRequestStatus::ACTIVE)
            ->orderBy('tr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return TeamRequest[]
     */
    public function findByPlayer(Player $player): array
    {
        return $this->createQueryBuilder('tr')
            ->where('tr.player = :player')
            ->setParameter('player', $player)
            ->orderBy('tr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveByTeamAndPlayer(Team $team, Player $player): ?TeamRequest
    {
        return $this->createQueryBuilder('tr')
            ->where('tr.team = :team')
            ->andWhere('tr.player = :player')
            ->andWhere('tr.status = :status')
            ->setParameter('team', $team)
            ->setParameter('player', $player)
            ->setParameter('status', TeamRequestStatus::ACTIVE)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function cancelActiveRequestsForPlayer(Player $player): int
    {
        return $this->createQueryBuilder('tr')
            ->update()
            ->set('tr.status', ':newStatus')
            ->where('tr.player = :player')
            ->andWhere('tr.status = :activeStatus')
            ->setParameter('newStatus', TeamRequestStatus::CANCELED)
            ->setParameter('player', $player)
            ->setParameter('activeStatus', TeamRequestStatus::ACTIVE)
            ->getQuery()
            ->execute();
    }
}
