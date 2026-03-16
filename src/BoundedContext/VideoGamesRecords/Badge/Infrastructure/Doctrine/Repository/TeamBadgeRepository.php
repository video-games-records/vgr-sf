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
     * @return array<array{libTeam: string, createdAt: \DateTime, endedAt: \DateTime|null, mbOrder: int|null}>
     */
    public function getHistoryForBadge(Badge $badge): array
    {
        return $this->createQueryBuilder('tb')
            ->select('t.libTeam, tb.createdAt, tb.endedAt, tb.mbOrder')
            ->join('tb.team', 't')
            ->where('tb.badge = :badge')
            ->setParameter('badge', $badge)
            ->orderBy('tb.endedAt', 'ASC')
            ->addOrderBy('tb.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
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
     * @return array<array{tbId: int, badgeId: int, badgeValue: int, nameEn: string, nameFr: string, mbOrder: ?int}>
     */
    public function getMasterBadgesForManagement(Team $team): array
    {
        return $this->getEntityManager()->createQuery("
            SELECT tb.id as tbId, mb.id as badgeId, mb.value as badgeValue, g.libGameEn as nameEn, g.libGameFr as nameFr, tb.mbOrder,
                   COALESCE(tb.mbOrder, 999999) as HIDDEN mbOrderSort
            FROM App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\MasterBadge mb
            JOIN mb.game g
            JOIN App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\TeamBadge tb WITH tb.badge = mb
            WHERE tb.team = :team
            AND tb.endedAt IS NULL
            ORDER BY mbOrderSort ASC, mb.value ASC
        ")
        ->setParameter('team', $team)
        ->getResult();
    }

    /**
     * @param array<int, int> $order Map of tbId => position
     */
    public function updateMasterBadgesOrder(Team $team, array $order): void
    {
        foreach ($order as $tbId => $position) {
            $teamBadge = $this->find($tbId);
            if ($teamBadge && $teamBadge->getTeam()->getId() === $team->getId()) {
                $teamBadge->setMbOrder($position);
                $this->getEntityManager()->persist($teamBadge);
            }
        }
        $this->getEntityManager()->flush();
    }

    /**
     * @param QueryBuilder $query
     */
    private function onlyActive(QueryBuilder $query): void
    {
        $query->andWhere($query->expr()->isNull('tb.endedAt'));
    }
}
