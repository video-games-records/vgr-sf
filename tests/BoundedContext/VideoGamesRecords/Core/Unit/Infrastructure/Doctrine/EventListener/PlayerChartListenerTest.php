<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\VideoGamesRecords\Core\Application\Manager\ScoreManager;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\EventListener\PlayerChartListener;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PlayerChartListenerTest extends TestCase
{
    /**
     * @return array{0: Chart&MockObject, 1: Group&MockObject, 2: Game&MockObject, 3: Serie&MockObject}
     */
    private function makeChain(int $chartNbPost, int $groupNbPost, int $gameNbPost, int $serieNbPost): array
    {
        $chart = $this->createMock(Chart::class);
        $chart->method('getNbPost')->willReturn($chartNbPost);

        $group = $this->createMock(Group::class);
        $group->method('getNbPost')->willReturn($groupNbPost);
        $chart->method('getGroup')->willReturn($group);

        $game = $this->createMock(Game::class);
        $game->method('getNbPost')->willReturn($gameNbPost);
        $group->method('getGame')->willReturn($game);

        $serie = $this->createMock(Serie::class);
        $serie->method('getNbPost')->willReturn($serieNbPost);
        $game->method('getSerie')->willReturn($serie);

        return [$chart, $group, $game, $serie];
    }

    private function makePlayerChart(Chart $chart): PlayerChart
    {
        $player = $this->createMock(Player::class);
        $player->method('getNbChart')->willReturn(0);

        $playerChart = $this->createMock(PlayerChart::class);
        $playerChart->method('getChart')->willReturn($chart);
        $playerChart->method('getPlayer')->willReturn($player);
        $playerChart->method('getPlatform')->willReturn($this->createMock(Platform::class));

        return $playerChart;
    }

    public function testPrePersistPropagatesNbPostIncrementUpToSerie(): void
    {
        [$chart, $group, $game, $serie] = $this->makeChain(2, 5, 10, 20);
        $chart->expects($this->once())->method('setNbPost')->with(3);
        $group->expects($this->once())->method('setNbPost')->with(6);
        $game->expects($this->once())->method('setNbPost')->with(11);
        $serie->expects($this->once())->method('setNbPost')->with(21);

        $playerChart = $this->makePlayerChart($chart);

        $em = $this->createMock(EntityManagerInterface::class);
        $lifecycleArgs = $this->createMock(LifecycleEventArgs::class);
        $lifecycleArgs->method('getObjectManager')->willReturn($em);

        $listener = new PlayerChartListener($this->createMock(ScoreManager::class));
        $listener->prePersist($playerChart, $lifecycleArgs);
    }

    public function testPrePersistDoesNotFailWhenGameHasNoSerie(): void
    {
        $chart = $this->createMock(Chart::class);
        $chart->method('getNbPost')->willReturn(0);

        $group = $this->createMock(Group::class);
        $group->method('getNbPost')->willReturn(0);
        $chart->method('getGroup')->willReturn($group);

        $game = $this->createMock(Game::class);
        $game->method('getNbPost')->willReturn(0);
        $game->method('getSerie')->willReturn(null);
        $group->method('getGame')->willReturn($game);

        $playerChart = $this->makePlayerChart($chart);

        $em = $this->createMock(EntityManagerInterface::class);
        $lifecycleArgs = $this->createMock(LifecycleEventArgs::class);
        $lifecycleArgs->method('getObjectManager')->willReturn($em);

        $listener = new PlayerChartListener($this->createMock(ScoreManager::class));
        $listener->prePersist($playerChart, $lifecycleArgs);

        $this->addToAssertionCount(1);
    }

    public function testPreRemovePropagatesNbPostDecrementDownToSerie(): void
    {
        [$chart, $group, $game, $serie] = $this->makeChain(2, 5, 10, 20);
        $chart->expects($this->once())->method('setNbPost')->with(1);
        $group->expects($this->once())->method('setNbPost')->with(4);
        $game->expects($this->once())->method('setNbPost')->with(9);
        $serie->expects($this->once())->method('setNbPost')->with(19);

        $playerChart = $this->makePlayerChart($chart);

        $listener = new PlayerChartListener($this->createMock(ScoreManager::class));
        $listener->preRemove($playerChart);
    }

    public function testPreUpdateMovingChartPropagatesIncrementAndDecrementToDistinctSeries(): void
    {
        [$oldChart, $oldGroup, $oldGame, $oldSerie] = $this->makeChain(2, 5, 10, 20);
        $oldChart->expects($this->once())->method('setNbPost')->with(1);
        $oldGroup->expects($this->once())->method('setNbPost')->with(4);
        $oldGame->expects($this->once())->method('setNbPost')->with(9);
        $oldSerie->expects($this->once())->method('setNbPost')->with(19);
        $oldChart->method('getLibs')->willReturn(new ArrayCollection());

        [$newChart, $newGroup, $newGame, $newSerie] = $this->makeChain(3, 6, 11, 21);
        $newChart->expects($this->once())->method('setNbPost')->with(4);
        $newGroup->expects($this->once())->method('setNbPost')->with(7);
        $newGame->expects($this->once())->method('setNbPost')->with(12);
        $newSerie->expects($this->once())->method('setNbPost')->with(22);
        $newChart->method('getLibs')->willReturn(new ArrayCollection());

        $playerChart = $this->createMock(PlayerChart::class);
        $playerChart->method('getLibs')->willReturn(new ArrayCollection());
        $playerChart->method('getRank')->willReturn(2);
        $playerChart->method('getStatus')->willReturn(PlayerChartStatusEnum::PROVED);
        $playerChart->method('getDateInvestigation')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);

        $event = $this->createMock(PreUpdateEventArgs::class);
        $event->method('getEntityChangeSet')->willReturn(['chart' => [$oldChart, $newChart]]);
        $event->method('getObjectManager')->willReturn($em);
        $event->method('hasChangedField')->willReturn(false);

        $listener = new PlayerChartListener($this->createMock(ScoreManager::class));
        $listener->preUpdate($playerChart, $event);
    }
}
