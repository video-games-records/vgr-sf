<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository;

use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use DateTime;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\Badge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlayerBadge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Event\PlayerMasterBadgeLost;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;

/**
 * @extends DefaultRepository<PlayerBadge>
 */
class PlayerBadgeRepository extends DefaultRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($registry, PlayerBadge::class);
    }

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
                //----- Dispatch event for Master badges (games)
                if ($game !== null && $badge->isTypeMaster()) {
                    $this->eventDispatcher->dispatch(new PlayerMasterBadgeLost($playerBadge, $game));
                }
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
     * @return array<array{badgeId: int, badgeValue: int, createdAt: \DateTime, nameEn: string, nameFr: string}>
     */
    public function getMasterBadgesDataForPlayer(Player $player): array
    {
        return $this->getEntityManager()->createQuery("
            SELECT mb.id as badgeId, mb.value as badgeValue, pb.createdAt, g.libGameEn as nameEn, g.libGameFr as nameFr
            FROM App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\MasterBadge mb
            JOIN mb.game g
            JOIN App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlayerBadge pb WITH pb.badge = mb
            WHERE pb.player = :player
            AND pb.endedAt IS NULL
            ORDER BY mb.value ASC
        ")
        ->setParameter('player', $player)
        ->getResult();
    }

    /**
     * @return array<array{badgeId: int, badgeValue: int, createdAt: \DateTime, name: string}>
     */
    public function getPlatformBadgesDataForPlayer(Player $player): array
    {
        return $this->getEntityManager()->createQuery("
            SELECT plb.id as badgeId, plb.value as badgeValue, pb.createdAt, pl.name as name
            FROM App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlatformBadge plb
            JOIN plb.platform pl
            JOIN App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlayerBadge pb WITH pb.badge = plb
            WHERE pb.player = :player
            AND pb.endedAt IS NULL
            ORDER BY plb.value ASC
        ")
        ->setParameter('player', $player)
        ->getResult();
    }

    /**
     * @return array<array{badgeId: int, badgeValue: int, createdAt: \DateTime, countryCode: string, nameEn: string, nameFr: string}>
     */
    public function getCountryBadgesDataForPlayer(Player $player): array
    {
        return $this->getEntityManager()->createQuery("
            SELECT cb.id as badgeId, cb.value as badgeValue, pb.createdAt,
                   c.codeIso2 as countryCode,
                   (SELECT tEn.name FROM App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\CountryTranslation tEn
                    WHERE tEn.translatable = c AND tEn.locale = 'en') as nameEn,
                   (SELECT tFr.name FROM App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\CountryTranslation tFr
                    WHERE tFr.translatable = c AND tFr.locale = 'fr') as nameFr
            FROM App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\CountryBadge cb
            JOIN cb.country c
            JOIN App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlayerBadge pb WITH pb.badge = cb
            WHERE pb.player = :player
            AND pb.endedAt IS NULL
            ORDER BY cb.value ASC
        ")
        ->setParameter('player', $player)
        ->getResult();
    }

    /**
     * @return array<array{badgeId: int, badgeValue: int, createdAt: \DateTime, name: string}>
     */
    public function getSerieBadgesDataForPlayer(Player $player): array
    {
        return $this->getEntityManager()->createQuery("
            SELECT sb.id as badgeId, sb.value as badgeValue, pb.createdAt, s.libSerie as name
            FROM App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\SerieBadge sb
            JOIN sb.serie s
            JOIN App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlayerBadge pb WITH pb.badge = sb
            WHERE pb.player = :player
            AND pb.endedAt IS NULL
            ORDER BY sb.value ASC
        ")
        ->setParameter('player', $player)
        ->getResult();
    }

    /**
     * @return array<array{pseudo: string, createdAt: \DateTime, endedAt: \DateTime|null, mbOrder: int|null}>
     */
    public function getHistoryForBadge(Badge $badge): array
    {
        return $this->createQueryBuilder('pb')
            ->select('p.pseudo, pb.createdAt, pb.endedAt, pb.mbOrder')
            ->join('pb.player', 'p')
            ->where('pb.badge = :badge')
            ->setParameter('badge', $badge)
            ->orderBy('pb.endedAt', 'ASC')
            ->addOrderBy('pb.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<array{badgeId: int, badgeValue: int, createdAt: \DateTime}>
     */
    public function getForumBadgesDataForPlayer(Player $player): array
    {
        return $this->getBadgesDataForPlayer($player, BadgeType::FORUM);
    }

    /**
     * @return array<array{badgeId: int, badgeValue: int, createdAt: \DateTime}>
     */
    public function getConnexionBadgesDataForPlayer(Player $player): array
    {
        return $this->getBadgesDataForPlayer($player, BadgeType::CONNEXION);
    }

    /**
     * @return array<array{badgeId: int, badgeValue: int, createdAt: \DateTime}>
     */
    public function getChartBadgesDataForPlayer(Player $player): array
    {
        return $this->getBadgesDataForPlayer($player, BadgeType::VGR_CHART);
    }

    /**
     * @return array<array{badgeId: int, badgeValue: int, createdAt: \DateTime}>
     */
    public function getProofBadgesDataForPlayer(Player $player): array
    {
        return $this->getBadgesDataForPlayer($player, BadgeType::VGR_PROOF);
    }

    /**
     * @return array<array{badgeId: int, badgeValue: int, createdAt: \DateTime}>
     */
    public function getDonationBadgesDataForPlayer(Player $player): array
    {
        return $this->getBadgesDataForPlayer($player, BadgeType::DON);
    }

    /**
     * @return array<array{badgeId: int, badgeValue: int, createdAt: \DateTime}>
     */
    private function getBadgesDataForPlayer(Player $player, BadgeType $badgeType): array
    {
        return $this->createQueryBuilder('pb')
            ->select('b.id as badgeId, b.value as badgeValue, pb.createdAt')
            ->join('pb.badge', 'b')
            ->where('pb.player = :player')
            ->andWhere('b.type = :badgeType')
            ->andWhere('pb.endedAt IS NULL')
            ->setParameter('player', $player)
            ->setParameter('badgeType', $badgeType)
            ->orderBy('b.value', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param QueryBuilder $query
     */
    private function onlyActive(QueryBuilder $query): void
    {
        $query->andWhere($query->expr()->isNull('pb.endedAt'));
    }
}
