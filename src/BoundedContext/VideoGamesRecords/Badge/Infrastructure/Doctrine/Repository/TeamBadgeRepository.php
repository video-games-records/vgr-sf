<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use DateTime;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\Badge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Event\TeamMasterBadgeLost;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\TeamBadge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;

/**
 * @extends DefaultRepository<TeamBadge>
 */
class TeamBadgeRepository extends DefaultRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($registry, TeamBadge::class);
    }

    /**
     * Récupère les badges d'une team selon le type et avec tri personnalisé
     *
     * @param Team $team la team
     * @param string|array<string> $badgeType Le type de badge (string) ou tableau de types
     * @param array<string, string> $orderBy Tableau associatif pour le tri (ex: ['badge.value' => 'DESC', 'createdAt' => 'ASC'])
     * @param bool $onlyActive Si true, ne retourne que les badges actifs (ended_at = null)
     * @return array<TeamBadge>
     */
    public function findByTeamAndType(
        Team $team,
        string|array $badgeType,
        array $orderBy = [],
        bool $onlyActive = true
    ): array {
        $qb = $this->createQueryBuilder('tb')
            ->distinct()
            ->join('tb.badge', 'b')
            ->addSelect('b')
            ->where('tb.team = :team')
            ->setParameter('team', $team);

        // Filtre sur le type de badge
        if (is_array($badgeType)) {
            $qb->andWhere('b.type IN (:badgeTypes)')
                ->setParameter('badgeTypes', $badgeType);
        } else {
            $qb->andWhere('b.type = :badgeType')
                ->setParameter('badgeType', $badgeType);
        }

        // Filtre sur les badges actifs si demandé
        if ($onlyActive) {
            $this->onlyActive($qb);
        }

        // Application du tri
        foreach ($orderBy as $field => $direction) {
            $qb->addOrderBy($field, $direction);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $badge
     * @return array
     */
    /**
     * @param Badge $badge
     * @return array<TeamBadge>
     */
    public function getFromBadge(Badge $badge): array
    {
        $query = $this->createQueryBuilder('tb');
        $query
            ->where('tb.badge = :badge')
            ->setParameter('badge', $badge);

        $this->onlyActive($query);

        return $query->getQuery()->getResult();
    }


    /**
     * @param array<int, int> $teams
     * @param Badge $badge
     * @param Game|null $game Game entity for MASTER badges (required for notifications)
     * @throws Exception
     * @throws ORMException
     */
    public function updateBadge(array $teams, Badge $badge, ?Game $game = null): void
    {
        //----- get teams with badge
        $list = $this->getFromBadge($badge);

        //----- Remove badge
        foreach ($list as $teamBadge) {
            $idTeam = $teamBadge->getTeam()->getId();
            //----- Remove badge
            if (!array_key_exists($idTeam, $teams)) {
                $teamBadge->setEndedAt(new DateTime());
                $this->getEntityManager()->persist($teamBadge);
                //----- Dispatch event for Master badges (games)
                if ($game !== null && $badge->isTypeMaster()) {
                    $this->eventDispatcher->dispatch(new TeamMasterBadgeLost($teamBadge, $game));
                }
            }
            $teams[$idTeam] = 1;
        }
        //----- Add badge
        foreach ($teams as $idTeam => $value) {
            if ($value == 0) {
                $teamBadge = new TeamBadge();
                /** @var Team $team */
                $team = $this->getEntityManager()
                    ->getReference(Team::class, $idTeam);
                $teamBadge->setTeam($team);
                $teamBadge->setBadge($badge);
                $this->getEntityManager()->persist($teamBadge);
            }
        }
    }

    /**
     * Get master badges data for a team without loading Badge entity relationships
     *
     * @param Team $team
     * @return array<array{badgeId: int, badgeValue: int, createdAt: \DateTime}>
     */
    public function getMasterBadgesDataForTeam(Team $team): array
    {
        return $this->createQueryBuilder('tb')
            ->select('b.id as badgeId, b.value as badgeValue, tb.createdAt')
            ->join('tb.badge', 'b')
            ->where('tb.team = :team')
            ->andWhere('b.type = :badgeType')
            ->andWhere('tb.endedAt IS NULL')
            ->setParameter('team', $team)
            ->setParameter('badgeType', BadgeType::MASTER)
            ->orderBy('b.value', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param QueryBuilder $query
     */
    private function onlyActive(QueryBuilder $query): void
    {
        $query->andWhere($query->expr()->isNull('tb.endedAt'));
    }
}
