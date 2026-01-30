<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Player\PlayerDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Player\PlayerStatsDTO;

readonly class PlayerMapper
{
    public function __construct(
        private CountryMapper $countryMapper
    ) {
    }
    public function toDTO(Player $player): PlayerDTO
    {
        return new PlayerDTO(
            id: $player->getId(),
            pseudo: $player->getPseudo(),
            slug: $player->getSlug(),
            nbConnexion: $player->getNbConnexion(),
            hasDonate: $player->getHasDonate(),
            stats: $this->toPlayerStatsDTO($player),
            lastLogin: $player->getLastLogin(),
            createdAt: $player->getCreatedAt(),
            presentation: $player->getPresentation(),
            collection: $player->getCollection(),
            country: $player->getCountry() ? $this->countryMapper->toDTO($player->getCountry()) : null,
            birthDate: $player->getBirthDate(),
        );
    }


    private function toPlayerStatsDTO(Player $player): PlayerStatsDTO
    {
        return new PlayerStatsDTO(
            pointGame: $player->getPointGame(),
            pointChart: $player->getPointChart(),
            pointBadge: $player->getPointBadge(),
            nbGame: $player->getNbGame(),
            nbChart: $player->getNbChart(),
            nbVideo: $player->getNbVideo(),
            nbMasterBadge: $player->getNbMasterBadge(),
            nbChartProven: $player->getNbChartProven(),
            nbChartMax: $player->getNbChartMax(),
            chartRank0: $player->getChartRank0(),
            chartRank1: $player->getChartRank1(),
            chartRank2: $player->getChartRank2(),
            chartRank3: $player->getChartRank3(),
            chartRank4: $player->getChartRank4(),
            chartRank5: $player->getChartRank5(),
            gameRank0: $player->getGameRank0(),
            gameRank1: $player->getGameRank1(),
            gameRank2: $player->getGameRank2(),
            gameRank3: $player->getGameRank3(),
            rankCup: $player->getRankCup(),
            rankMedal: $player->getRankMedal(),
            rankBadge: $player->getRankBadge(),
            rankPointChart: $player->getRankPointChart(),
            rankPointGame: $player->getRankPointGame(),
            rankCountry: $player->getRankCountry(),
            rankProof: $player->getRankProof(),
            averageChartRank: $player->getAverageChartRank(),
            averageGameRank: $player->getAverageGameRank(),
        );
    }
}
