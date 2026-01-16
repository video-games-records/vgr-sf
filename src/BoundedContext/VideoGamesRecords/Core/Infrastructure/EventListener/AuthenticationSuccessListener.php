<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\EventListener;

use Doctrine\ORM\Exception\ORMException;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\DataTransformer\UserToPlayerTransformer;

readonly class AuthenticationSuccessListener
{
    public function __construct(
        private UserToPlayerTransformer $userToPlayerTransformer
    ) {
    }

    /**
     * @throws ORMException
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        $data = $event->getData();
        $user = $event->getUser();

        $player = $this->userToPlayerTransformer->transform($user);
        $data['player'] = [
            'id' => $player->getId(),
            'slug' => $player->getSlug(),
            'pseudo' => $player->getPseudo(),
            'team' => null,
        ];

        $friendsIds = $player->getFriends()->map(fn($friend) => $friend->getId())->toArray();
        $friendsIds[] = $player->getId();
        $data['friends'] = $friendsIds;

        $team = $player->getTeam();
        if ($team !== null) {
            $data['player']['team'] = [
                'id' => $team->getId(),
                'slug' => $team->getSlug(),
                'tag' => $team->getTag(),
                'libTeam' => $team->getLibTeam()
            ];
        }

        $event->setData($data);
    }
}
