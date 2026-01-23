<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository;

use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use DateTime;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\Badge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlayerBadge;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;

/**
 * @extends DefaultRepository<PlayerBadge>
 */
class PlayerBadgeRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerBadge::class);
    }

    /**
     * Récupère les badges d'un joueur selon le type et avec tri personnalisé
     *
     * @param Player $player Le joueur
     * @param string|array<string> $badgeType Le type de badge (string) ou tableau de types
     * @param array<string, string> $orderBy Tableau associatif pour le tri (ex: ['badge.value' => 'DESC', 'createdAt' => 'ASC'])
     * @param bool $onlyActive Si true, ne retourne que les badges actifs (ended_at = null)
     * @return array<PlayerBadge>
     */
    public function findByPlayerAndType(
        Player $player,
        string|array $badgeType,
        array $orderBy = [],
        bool $onlyActive = true
    ): array {
        $qb = $this->createQueryBuilder('pb')
            ->join('pb.badge', 'b')
            ->where('pb.player = :player')
            ->setParameter('player', $player);

        // Filtre sur le type de badge
        if (is_array($badgeType)) {
            $qb->andWhere('b.type IN (:badgeTypes)')
                ->setParameter('badgeTypes', $badgeType);
        } else {
            $qb->andWhere('b.type = :badgeType')
                ->setParameter('badgeType', $badgeType);

            // Jointures pour optimiser les requêtes mais sans addSelect pour éviter les problèmes de type
            if ($badgeType === BadgeType::MASTER->value) {
                $qb->leftJoin('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game', 'g', 'WITH', 'g.badge = b');
            }

            if ($badgeType === BadgeType::SERIE->value) {
                $qb->leftJoin('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie', 's', 'WITH', 's.badge = b');
            }

            if ($badgeType === BadgeType::PLATFORM->value) {
                $qb->leftJoin('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform', 'p', 'WITH', 'p.badge = b');
            }
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
     * @return array<PlayerBadge>
     */
    public function getFromBadge(Badge $badge): array
    {
        $query = $this->createQueryBuilder('pb');
        $query
            ->where('pb.badge = :badge')
            ->setParameter('badge', $badge);

        $this->onlyActive($query);

        return $query->getQuery()->getResult();
    }

    /**
     * @param array<int, int> $players
     * @param Badge $badge
     * @param Game|null $game Game entity for MASTER badges (required for value calculation)
     * @throws Exception|ORMException
     */
    public function updateBadge(array $players, Badge $badge, ?Game $game = null): void
    {
        //----- get players with badge
        $list = $this->getFromBadge($badge);

        //----- Remove badge
        foreach ($list as $playerBadge) {
            $idPlayer = $playerBadge->getPlayer()->getId();
            //----- Remove badge
            if ($idPlayer !== null && !array_key_exists($idPlayer, $players)) {
                $playerBadge->setEndedAt(new DateTime());
                $this->getEntityManager()->persist($playerBadge);
            }
            if ($idPlayer !== null) {
                $players[$idPlayer] = 1;
            }
        }
        //----- Add badge
        foreach ($players as $idPlayer => $value) {
            if (0 === $value) {
                $playerBadge = new PlayerBadge();
                /** @var Player $player */
                $player = $this->getEntityManager()
                    ->getReference('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player', $idPlayer);
                $playerBadge->setPlayer($player);
                $playerBadge->setBadge($badge);
                $this->getEntityManager()->persist($playerBadge);
            }
        }
        $badge->setNbPlayer(count($players));
        $badge->majValue($game);
    }

    /**
     * @param QueryBuilder $query
     */
    private function onlyActive(QueryBuilder $query): void
    {
        $query->andWhere($query->expr()->isNull('pb.endedAt'));
    }
}
