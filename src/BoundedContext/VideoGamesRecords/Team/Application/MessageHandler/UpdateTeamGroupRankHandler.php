<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Application\MessageHandler;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;
use App\SharedKernel\Domain\Exception\EntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use App\BoundedContext\VideoGamesRecords\Team\Application\Message\UpdateTeamGameRank;
use App\BoundedContext\VideoGamesRecords\Team\Application\Message\UpdateTeamGroupRank;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Tools\RankingTools;
use Zenstruck\Messenger\Monitor\Stamp\DescriptionStamp;

#[AsMessageHandler]
readonly class UpdateTeamGroupRankHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $bus,
    ) {
    }

    /**
     * @throws ORMException
     * @throws ExceptionInterface|EntityNotFoundException
     */
    public function __invoke(UpdateTeamGroupRank $updateTeamGroupRank): void
    {
        /** @var Group|null $group */
        $group = $this->em->getRepository('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group')
            ->find($updateTeamGroupRank->getGroupId());

        if (null === $group) {
            throw new EntityNotFoundException('Group', $updateTeamGroupRank->getGroupId());
        }

        //----- delete
        $query = $this->em->createQuery(
            'DELETE App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamGroup tg WHERE tg.group = :group'
        );
        $query->setParameter('group', $group);
        $query->execute();

        //----- select ans save result in array
        $query = $this->em->createQuery("
            SELECT
                t.id,
                '' as rankPointChart,
                '' as rankMedal,
                SUM(tc.chartRank0) as chartRank0,
                SUM(tc.chartRank1) as chartRank1,
                SUM(tc.chartRank2) as chartRank2,
                SUM(tc.chartRank3) as chartRank3,
                SUM(tc.pointChart) as pointChart
            FROM App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamChart tc
            JOIN tc.chart c
            JOIN tc.team t
            WHERE c.group = :group
            GROUP BY t.id
            ORDER BY pointChart DESC");


        $query->setParameter('group', $group);
        $result = $query->getResult();

        $list = [];
        foreach ($result as $row) {
            $list[] = $row;
        }

        //----- add some data
        $list = RankingTools::addRank($list, 'rankPointChart', ['pointChart'], true);
        $list = RankingTools::order(
            $list,
            [
                'chartRank0' => SORT_DESC,
                'chartRank1' => SORT_DESC,
                'chartRank2' => SORT_DESC,
                'chartRank3' => SORT_DESC
            ]
        );
        $list = RankingTools::addRank($list, 'rankMedal', ['chartRank0', 'chartRank1', 'chartRank2', 'chartRank3']);

        $normalizer = new ObjectNormalizer();
        $serializer = new Serializer([$normalizer]);

        foreach ($list as $row) {
            $teamGroup = $serializer->denormalize(
                $row,
                'App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamGroup'
            );
            $teamGroup->setTeam(
                $this->em->getReference('App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team', $row['id'])
            );
            $teamGroup->setGroup($group);

            $this->em->persist($teamGroup);
        }
        $this->em->flush();

        $this->bus->dispatch(
            new UpdateTeamGameRank($group->getGame()->getId()),
            [
                new DescriptionStamp(
                    sprintf('Update team-ranking for game [%d]', $group->getGame()->getId())
                )
            ]
        );
    }
}
