<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Serie\SerieDTO;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;

class SerieMapper
{
    public function toDTO(Serie $serie): SerieDTO
    {
        return new SerieDTO(
            id: (int) $serie->getId(),
            name: $serie->getName(),
            picture: $serie->getPicture(),
            status: $serie->getStatus(),
            nbChart: $serie->getNbChart(),
            nbGame: $serie->getNbGame(),
            nbPlayer: $serie->getNbPlayer(),
            nbTeam: $serie->getNbTeam(),
            slug: $serie->getSlug()
        );
    }
}
