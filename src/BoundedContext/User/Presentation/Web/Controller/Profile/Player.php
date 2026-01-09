<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Web\Controller\Profile;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class Player extends AbstractController
{
    #[Route('/profile/player', name: 'app_profile_player', methods: ['GET'])]
    public function __invoke(): Response
    {
        $user = $this->getUser();

        // TODO: Display player statistics and achievements
        return $this->render('@User/profile/player.html.twig');
    }
}
