<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Presentation\Web\Controller\Avatar;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use App\SharedKernel\Infrastructure\FileSystem\Manager\AvatarManager;

class GetTeamAvatar extends AbstractController
{
    private AvatarManager $avatarManager;


    public function __construct(AvatarManager $teamAvatarManager)
    {
        $this->avatarManager = $teamAvatarManager;
    }

    #[Route(
        '/teams/{id}/avatar',
        name: 'vgr_team_avatar',
        methods: ['GET'],
        requirements: ['id' => '[1-9]\d*']
    )]
    public function __invoke(Team $team): StreamedResponse
    {
        $response = $this->avatarManager->read($team->getLogo());
        $response->setPublic();
        $response->setMaxAge(3600);
        return $response;
    }
}
