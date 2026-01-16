<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\MessageHandler\Player;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;
use App\SharedKernel\Domain\Exception\EntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerPlatformRank;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Tools\RankingTools;

#[AsMessageHandler]
readonly class UpdatePlayerPlatformRankHandler
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function __invoke(UpdatePlayerPlatformRank $updatePlayerPlatformRank): void
    {
        /** @var Platform|null $platform */
        $platform = $this->em->getRepository('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform')->find(
            $updatePlayerPlatformRank->getPlatformId()
        );

        if (null === $platform) {
            throw new EntityNotFoundException('Platform', $updatePlayerPlatformRank->getPlatformId());
        }

        // Delete old data
        $query = $this->em->createQuery(
            'DELETE App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerPlatform pp WHERE pp.platform = :platform'
        );
        $query->setParameter('platform', $platform);
        $query->execute();

        // Select data
        $query = $this->em->createQuery("
            SELECT
                p.id,
                ifnull(SUM(pc.pointPlatform), 0) as pointPlatform,
                COUNT(pc) as nbChart
            FROM App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart pc
            JOIN pc.player p
            JOIN pc.platform pl
            WHERE pl.id = :idPlatform
            GROUP BY p.id
            ORDER BY pointPlatform DESC");

        $query->setParameter('idPlatform', $platform->getId());
        $result = $query->getResult();

        $list = [];
        foreach ($result as $row) {
            $list[] = $row;
        }

        $list = RankingTools::addRank($list, 'rankPointPlatform', ['pointPlatform']);
        $normalizer = new ObjectNormalizer();
        $serializer = new Serializer([$normalizer]);

        foreach ($list as $row) {
            $playerPlatform = $serializer->denormalize(
                $row,
                'App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerPlatform'
            );
            $playerPlatform->setPlayer(
                $this->em->getReference('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player', $row['id'])
            );
            $playerPlatform->setPlatform($platform);

            $this->em->persist($playerPlatform);
        }
        $this->em->flush();

        // Update badges directly (was in PlayerPlatformUpdatedSubscriber - now optimized)
        if ($platform->getBadge()) {
            // Get first place players from the ranking we just calculated
            $firstPlacePlayers = [];
            foreach ($list as $row) {
                if ($row['rankPointPlatform'] === 1) {
                    $firstPlacePlayers[$row['id']] = 0;
                } else {
                    break; // Rankings are ordered, so no more first places
                }
            }

            $this->em->getRepository('App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlayerBadge')
                ->updateBadge($firstPlacePlayers, $platform->getBadge());
        }
    }
}
