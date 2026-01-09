<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Web\Controller\Profile;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class ProfileRedirect extends AbstractController
{
    #[Route('/profile', name: 'app_profile_index', methods: ['GET'])]
    public function __invoke(): Response
    {
        // Redirect /profile to /profile/password by default
        return $this->redirectToRoute('app_profile_password');
    }
}
