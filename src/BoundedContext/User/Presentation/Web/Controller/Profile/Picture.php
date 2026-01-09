<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Web\Controller\Profile;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class Picture extends AbstractController
{
    #[Route('/profile/picture', name: 'app_profile_picture', methods: ['GET', 'POST'])]
    public function __invoke(): Response
    {
        $user = $this->getUser();

        // TODO: Implement profile picture upload form
        return $this->render('@User/profile/picture.html.twig');
    }
}
