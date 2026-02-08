<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Game\GameDTO;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;

class GameMapper
{
    public function toDTO(Game $game): GameDTO
    {
        // Map serie with serie:read fields
        $serie = null;
        if ($game->getSerie() !== null) {
            $serie = [
                'id' => (int) $game->getSerie()->getId(),
                'name' => $game->getSerie()->getName(),
                'slug' => $game->getSerie()->getSlug()
            ];
        }

        // Map platforms with platform:read fields
        $platforms = [];
        foreach ($game->getPlatforms() as $platform) {
            $platforms[] = [
                'id' => (int) $platform->getId(),
                'name' => $platform->getName(),
                'slug' => $platform->getSlug()
            ];
        }

        // Map genres with genre:read fields
        $genres = [];
        foreach ($game->getGenres() as $genre) {
            $genres[] = [
                'id' => (int) $genre->getId(),
                'name' => $genre->getName(),
                'slug' => $genre->getSlug()
            ];
        }

        return new GameDTO(
            id: (int) $game->getId(),
            name: $game->getName(),
            picture: $game->getPicture(),
            status: $game->getStatus(),
            publishedAt: $game->getPublishedAt(),
            isRank: $game->getIsRank(),
            nbChart: $game->getNbChart(),
            nbPost: $game->getNbPost(),
            nbPlayer: $game->getNbPlayer(),
            nbTeam: $game->getNbTeam(),
            releaseDate: $game->getReleaseDate(),
            slug: $game->getSlug(),
            downloadUrl: $game->getDownloadUrl(),
            lastUpdate: $game->getLastUpdate(),
            serie: $serie,
            platforms: $platforms,
            genres: $genres
        );
    }
}
