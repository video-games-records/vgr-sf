<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security;

use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\DataTransformer\UserToPlayerTransformer;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;

class UserProvider
{
    private Security $security;
    private UserToPlayerTransformer $userToPlayerTransformer;

    public function __construct(Security $security, UserToPlayerTransformer $userToPlayerTransformer)
    {
        $this->security = $security;
        $this->userToPlayerTransformer = $userToPlayerTransformer;
    }

    public function getUser(): ?UserInterface
    {
        return $this->security->getUser();
    }

    /**
     * @throws ORMException
     */
    public function getPlayer(): ?Player
    {
        if (!$this->security->getUser()) {
            return null;
        }
        return $this->userToPlayerTransformer->transform($this->security->getUser());
    }

    /**
     * @throws ORMException
     */
    public function getTeam(): ?Team
    {
        $player = $this->userToPlayerTransformer->transform($this->security->getUser());
        return $player->getTeam();
    }
}
