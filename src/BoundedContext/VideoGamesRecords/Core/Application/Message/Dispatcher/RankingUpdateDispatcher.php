<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Message\Dispatcher;

use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerChartRank;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerData;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerRank;
use App\BoundedContext\VideoGamesRecords\Team\Application\Message\UpdateTeamChartRank;
use Zenstruck\Messenger\Monitor\Stamp\DescriptionStamp;

readonly class RankingUpdateDispatcher
{
    public function __construct(
        private MessageBusInterface $bus,
    ) {
    }

    /**
     * @param Game $game
     * @throws ExceptionInterface
     */
    public function updatePlayerRankFromGame(Game $game): void
    {
        foreach ($game->getGroups() as $group) {
            $this->updatePlayerRankFromGroup($group);
        }
    }

    /**
     * @param Group $group
     * @throws ExceptionInterface
     */
    public function updatePlayerRankFromGroup(Group $group): void
    {
        foreach ($group->getCharts() as $chart) {
            $this->bus->dispatch(new UpdatePlayerChartRank($chart->getId()));
        }
    }

    /**
     * @param Player $player
     * @throws ExceptionInterface
     */
    public function updateTeamRankFromPlayer(Player $player): void
    {
        /** @var PlayerChart $playerChart */
        foreach ($player->getPlayerCharts() as $playerChart) {
            $this->bus->dispatch(new UpdateTeamChartRank($playerChart->getChart()->getId()));
        }
    }

    /**
     * @param Player $player
     * @throws ExceptionInterface
     */
    public function updatePlayerRankFromPlayer(Player $player): void
    {
        $this->bus->dispatch(
            new UpdatePlayerData($player->getId()),
            [
                new DescriptionStamp(
                    sprintf('Update player-data for player [%d]', $player->getId())
                )
            ]
        );
        $this->bus->dispatch(new UpdatePlayerRank());
    }
}
