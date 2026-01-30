<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\Doctrine\EventListener\User;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;

#[AsEntityListener(event: Events::postUpdate, method: 'updatePlayer', entity: User::class)]
readonly class UpdatePlayerListener
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function updatePlayer(User $user): void
    {
        /** @var Player|null $player */
        $player = $this->em->getRepository('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player')
            ->findOneBy(['user_id' => $user->getId()]);

        if ($player === null) {
            return;
        }

        $player->setPseudo($user->getUsername());
        $player->setAvatar($user->getAvatar());
        $player->setLastLogin($user->getLastlogin());
        $player->setNbConnexion($user->getNbConnexion());

        $this->em->flush();
    }
}
