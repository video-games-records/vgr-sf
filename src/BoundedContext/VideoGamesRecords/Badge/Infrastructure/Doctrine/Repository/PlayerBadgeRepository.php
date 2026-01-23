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
     * @return array<array{badgeId: int, badgeValue: int, createdAt: \DateTime}>
     */
    public function getMasterBadgesDataForPlayer(Player $player): array
    {
        return $this->getBadgesDataForPlayer($player, BadgeType::MASTER);
    }

    /**
     * @return array<array{badgeId: int, badgeValue: int, createdAt: \DateTime}>
     */
    public function getPlatformBadgesDataForPlayer(Player $player): array
    {
        return $this->getBadgesDataForPlayer($player, BadgeType::PLATFORM);
    }

    /**
     * @return array<array{badgeId: int, badgeValue: int, createdAt: \DateTime}>
     */
    public function getSerieBadgesDataForPlayer(Player $player): array
    {
        return $this->getBadgesDataForPlayer($player, BadgeType::SERIE);
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
