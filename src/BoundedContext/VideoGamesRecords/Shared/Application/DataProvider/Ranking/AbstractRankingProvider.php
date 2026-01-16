<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Application\DataProvider\Ranking;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Contracts\Ranking\RankingProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;

abstract class AbstractRankingProvider implements RankingProviderInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        private readonly UserProvider $userProvider
    ) {
    }

    /**
     * @throws ORMException
     */
    protected function getPlayer(): ?Player
    {
        return $this->userProvider->getPlayer();
    }

    /**
     * @throws ORMException
     */
    protected function getTeam(): ?Team
    {
        return $this->userProvider->getTeam();
    }
}
