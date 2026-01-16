<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\EventListener;

use DateTime;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\ProofRequest;
use App\BoundedContext\VideoGamesRecords\Core\Application\Manager\ScoreManager;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\ProofStatus;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: PlayerChart::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: PlayerChart::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: PlayerChart::class)]
class PlayerChartListener
{
    /** @var array<string, array{0: mixed, 1: mixed}> */
    private array $changeSet = [];

    public function __construct(
        private readonly ScoreManager $scoreManager
    ) {
    }

    /**
     * @param PlayerChart $playerChart
     * @param LifecycleEventArgs $event
     * @throws ORMException
     */
    public function prePersist(PlayerChart $playerChart, LifecycleEventArgs $event): void
    {
        $em = $event->getObjectManager();
        $playerChart->setStatus(PlayerChartStatusEnum::NONE);
        $playerChart->setLastUpdate(new DateTime());

        // Chart
        $chart = $playerChart->getChart();
        $this->incrementeNbPost($chart);


        // Group
        $group = $chart->getGroup();

        // Game
        $game = $group->getGame();
        $game->setLastUpdate(new DateTime());
        $game->setLastScore($playerChart);

        // Player
        $player = $playerChart->getPlayer();
        $player->setNbChart($player->getNbChart() + 1);

        // Set platform
        if (null === $playerChart->getPlatform()) {
            $playerChart->setPlatform($this->scoreManager->getPlatform($player, $game));
        }
    }


    /**
     * @param PlayerChart $playerChart
     * @param PreUpdateEventArgs $event
     * @throws ORMException
     */
    public function preUpdate(PlayerChart $playerChart, PreUpdateEventArgs $event): void
    {
        $this->changeSet = $event->getEntityChangeSet();
        $em = $event->getObjectManager();

        // Move score
        if (array_key_exists('chart', $this->changeSet)) {
            $newChart = $this->changeSet['chart'][1];
            $oldChart = $this->changeSet['chart'][0];

            $this->incrementeNbPost($newChart);
            $this->decrementeNbPost($oldChart);

            $newChartLibs = $newChart->getLibs();
            foreach ($playerChart->getLibs() as $lib) {
                $lib->setLibChart($newChartLibs->current());
            }
        }

        $playerChart->setIsTopScore(false);
        if ($playerChart->getRank() === 1) {
            $playerChart->setIsTopScore(true);
        }

        if ($playerChart->getStatus() === PlayerChartStatusEnum::NONE) {
            $proof = $playerChart->getProof();
            $proof?->setStatus(ProofStatus::CLOSED);
            $playerChart->setProof(null);
        }

        //-- status
        if ($playerChart->getStatus() === PlayerChartStatusEnum::UNPROVED) {
            $playerChart->setPointChart(0);
            $playerChart->setRank(0);
            $playerChart->setIsTopScore(false);
        }

        $this->updateDateInvestigation($playerChart);

        $this->updateProof($playerChart, $em);

        $player = $playerChart->getPlayer();

        if ($event->hasChangedField('status')) {
            $oldStatus = $event->getOldValue('status');
            $newStatus = $event->getNewValue('status');

            if ($newStatus === PlayerChartStatusEnum::PROVED) {
                $player->setNbChartProven($player->getNbChartProven() + 1);
            }

            if ($oldStatus === PlayerChartStatusEnum::PROVED) {
                $player->setNbChartProven($player->getNbChartProven() - 1);
            }

            if ($newStatus === PlayerChartStatusEnum::UNPROVED) {
                $player->setNbChartDisabled($player->getNbChartDisabled() + 1);
            }

            if ($oldStatus === PlayerChartStatusEnum::UNPROVED) {
                $player->setNbChartDisabled($player->getNbChartDisabled() - 1);
            }
        }
    }

    /**
    /**
     * @param PlayerChart $playerChart
     * @param LifecycleEventArgs $event
     */
    public function preRemove(PlayerChart $playerChart, LifecycleEventArgs $event): void
    {
        // Chart
        $chart = $playerChart->getChart();
        $this->decrementeNbPost($chart);


        // Player
        $player = $playerChart->getPlayer();
        $player->setNbChart($player->getNbChart() - 1);
    }


    /**
     * @param PlayerChart $playerChart
     * @return void
     */
    private function updateDateInvestigation(PlayerChart $playerChart): void
    {
        if (
            null === $playerChart->getDateInvestigation()
            && PlayerChartStatusEnum::REQUEST_VALIDATED === $playerChart->getStatus()
        ) {
            $playerChart->setDateInvestigation(new DateTime());
        }

        if (
            null !== $playerChart->getDateInvestigation()
            && in_array(
                $playerChart->getStatus(),
                [PlayerChartStatusEnum::PROVED, PlayerChartStatusEnum::UNPROVED],
                true
            )
        ) {
            $playerChart->setDateInvestigation(null);
        }
    }

    /**
     * @param PlayerChart $playerChart
     * @param EntityManagerInterface $em
     * @return void
     */
    private function updateProof(PlayerChart $playerChart, EntityManagerInterface $em): void
    {
        if (
            array_key_exists('proof', $this->changeSet)
            && $this->changeSet['proof'][1] !== null
            && $playerChart->getStatus() === PlayerChartStatusEnum::REQUEST_PROOF_SENT
        ) {
            /** @var ProofRequest $proofRequest */
            $proofRequest = $em->getRepository('App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\ProofRequest')
                ->findOneBy(
                    [
                        'playerChart' => $playerChart
                    ],
                    ['createdAt' => 'DESC']
                );

            if ($proofRequest) {
                $playerChart->getProof()->setProofRequest($proofRequest);
            }
        }
    }


    private function incrementeNbPost(Chart $chart): void
    {
        // Chart
        $chart->setNbPost($chart->getNbPost() + 1);
        // Group
        $group = $chart->getGroup();
        $group->setNbPost($group->getNbPost() + 1);
        // Game
        $game = $group->getGame();
        $game->setNbPost($game->getNbPost() + 1);
    }

    private function decrementeNbPost(Chart $chart): void
    {
        // Chart
        $chart->setNbPost($chart->getNbPost() - 1);
        // Group
        $group = $chart->getGroup();
        $group->setNbPost($group->getNbPost() - 1);
        // Game
        $game = $group->getGame();
        $game->setNbPost($game->getNbPost() - 1);
    }
}
