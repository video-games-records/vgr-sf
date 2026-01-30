<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository;

use Doctrine\DBAL\Exception;
use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\LostPosition;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;

/**
 * @extends DefaultRepository<LostPosition>
 */
class LostPositionRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LostPosition::class);
    }


    /**
     * @param $player
     * @return int|mixed|string
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getNbLostPosition(Player $player): mixed
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)');
        $this->wherePlayer($qb, $player);
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $player
     * @return int|mixed|string
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getNbNewLostPosition(Player $player): mixed
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)');
        $this->wherePlayer($qb, $player);
        $qb->andWhere('l.createdAt > :now')
            ->setParameter('now', $player->getLastDisplayLostPosition());
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws Exception
     */
    public function purge(): void
    {
        $sql = "DELETE vgr_lostposition
        FROM vgr_lostposition
            INNER JOIN vgr_player_chart ON vgr_lostposition.player_id = vgr_player_chart.player_id AND vgr_lostposition.chart_id = vgr_player_chart.chart_id
        WHERE (vgr_player_chart.rank <= vgr_lostposition.old_rank)
        OR (vgr_player_chart.rank = 1 AND vgr_player_chart.nb_equal = 1 AND vgr_lostposition.old_rank = 0)";
        $this->getEntityManager()->getConnection()->executeStatement($sql);
    }


    /**
     * Récupère les lost positions paginées avec filtrage optionnel par jeu
     * @return array{items: array<LostPosition>, total: int, pages: int}
     */
    public function findByPlayerPaginated(Player $player, ?int $gameId, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('l')
            ->select('l', 'c', 'g', 'game')
            ->join('l.chart', 'c')
            ->join('c.group', 'g')
            ->join('g.game', 'game');

        $this->wherePlayer($qb, $player);

        if ($gameId !== null) {
            $qb->andWhere('game.id = :gameId')
                ->setParameter('gameId', $gameId);
        }

        $qb->orderBy('l.createdAt', 'DESC');

        // Get total count
        $countQb = clone $qb;
        $countQb->select('COUNT(l.id)');
        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        // Get paginated results
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();
        $pages = (int) ceil($total / $limit);

        return [
            'items' => $items,
            'total' => $total,
            'pages' => $pages,
        ];
    }

    /**
     * Récupère les jeux où le joueur a des lost positions (pour le dropdown)
     * @return array<Game>
     */
    public function getGamesWithLostPositions(Player $player): array
    {
        $gameIds = $this->createQueryBuilder('l')
            ->select('DISTINCT IDENTITY(g.game)')
            ->join('l.chart', 'c')
            ->join('c.group', 'g')
            ->where('l.player = :player')
            ->setParameter('player', $player)
            ->getQuery()
            ->getSingleColumnResult();

        if (empty($gameIds)) {
            return [];
        }

        return $this->getEntityManager()
            ->getRepository(Game::class)
            ->createQueryBuilder('game')
            ->where('game.id IN (:ids)')
            ->setParameter('ids', $gameIds)
            ->orderBy('game.libGameEn', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Supprime plusieurs lost positions par IDs (vérifie ownership)
     */
    public function deleteByIdsForPlayer(array $ids, Player $player): int
    {
        if (empty($ids)) {
            return 0;
        }

        return $this->createQueryBuilder('l')
            ->delete()
            ->where('l.id IN (:ids)')
            ->andWhere('l.player = :player')
            ->setParameter('ids', $ids)
            ->setParameter('player', $player)
            ->getQuery()
            ->execute();
    }

    /**
     * @param QueryBuilder $query
     * @param              $player
     */
    private function wherePlayer(QueryBuilder $query, Player $player): void
    {
        $query->where('l.player = :player')
            ->setParameter('player', $player);
    }
}
