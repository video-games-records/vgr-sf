<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Ranking\PlayerRankingDTO;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerGame;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerSerie;

class PlayerRankingMapper
{
    public function fromPlayerGame(PlayerGame $playerGame): PlayerRankingDTO
    {
        return $this->toDTO($playerGame->getPlayer(), $playerGame);
    }

    public function fromPlayerSerie(PlayerSerie $playerSerie): PlayerRankingDTO
    {
        return $this->toDTO($playerSerie->getPlayer(), $playerSerie);
    }

    private function toDTO(Player $player, PlayerGame|PlayerSerie $entity): PlayerRankingDTO
    {
        $country = null;
        if ($player->getCountry() !== null) {
            $country = [
                'id' => (int) $player->getCountry()->getId(),
                'name' => (string) $player->getCountry()->getName(),
                'codeIso2' => $player->getCountry()->getCodeIso2(),
            ];
        }

        $team = null;
        if ($player->getTeam() !== null) {
            $team = [
                'id' => (int) $player->getTeam()->getId(),
                'name' => $player->getTeam()->getName(),
                'slug' => $player->getTeam()->getSlug(),
            ];
        }

        $playerData = [
            'id' => (int) $player->getId(),
            'pseudo' => $player->getPseudo(),
            'slug' => $player->getSlug(),
            'country' => $country,
            'team' => $team,
        ];

        return new PlayerRankingDTO(
            id: (int) $player->getId(),
            rank: $entity->getRankPointChart(),
            pointChart: $entity->getPointChart(),
            nbChart: $entity->getNbChart(),
            nbChartProven: $entity->getNbChartProven(),
            platinum: $entity->getChartRank0(),
            gold: $entity->getChartRank1(),
            silver: $entity->getChartRank2(),
            bronze: $entity->getChartRank3(),
            player: $playerData,
        );
    }
}
