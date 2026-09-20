<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Manager;

use Doctrine\ORM\EntityManagerInterface;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;

readonly class SerieManager
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function updatePicture(Serie $serie, string $filename): void
    {
        $serie->setPicture($filename);
        $this->em->flush();
    }
}
