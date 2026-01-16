<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository;

use Doctrine\Persistence\ManagerRegistry;
use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Discord;

class DiscordRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Discord::class);
    }
}
