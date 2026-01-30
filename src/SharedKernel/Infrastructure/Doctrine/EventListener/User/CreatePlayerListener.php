<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\Doctrine\EventListener\User;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerStatusEnum;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\PlayerBadge;

#[AsEntityListener(event: Events::postPersist, method: 'createPlayer', entity: User::class)]
readonly class CreatePlayerListener
{
    public const int GROUP_PLAYER = 2;
    public const int BADGE_REGISTER = 1;

    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function createPlayer(User $user): void
    {
        // Role Player (only if the group exists)
        $group = $this->em
            ->find('App\BoundedContext\User\Domain\Entity\Group', self::GROUP_PLAYER);
        if ($group) {
            $user->addGroup($group);
        }

        // Player
        $player = new Player();
        $player->setId($user->getId());
        $player->setUserId($user->getId());
        $player->setPseudo($user->getUsername());
        $player->setStatus(PlayerStatusEnum::MEMBER);

        $this->em->persist($player);

        // Register Badge (only if it exists)
        $badge = $this->em
            ->find('App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\Badge', self::BADGE_REGISTER);
        if ($badge) {
            $playerBadge = new PlayerBadge();
            $playerBadge->setPlayer($player);
            $playerBadge->setBadge($badge);
            $this->em->persist($playerBadge);
        }

        $this->em->flush();
    }
}
