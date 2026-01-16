<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Application\DataProvider\Ranking;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Contracts\Ranking\RankingProviderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\DataTransformer\UserToPlayerTransformer;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use App\BoundedContext\User\Domain\Entity\User;

abstract class AbstractRankingProvider implements RankingProviderInterface
{
    protected EntityManagerInterface $em;
    protected UserToPlayerTransformer $userToPlayerTransformer;

    public function __construct(
        EntityManagerInterface $em,
        UserToPlayerTransformer $userToPlayerTransformer
    ) {
        $this->em = $em;
        $this->userToPlayerTransformer = $userToPlayerTransformer;
    }

    /**
     * @throws ORMException
     */
    protected function getPlayer(?User $user = null): ?Player
    {
        if ($user === null) {
            return null;
        }
        return $this->userToPlayerTransformer->transform($user);
    }

    /**
     * @throws ORMException
     */
    protected function getTeam(?User $user = null): ?Team
    {
        if ($user === null) {
            return null;
        }
        $player = $this->userToPlayerTransformer->transform($user);
        return $player->getTeam();
    }
}
