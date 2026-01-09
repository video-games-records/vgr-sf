<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Web\Controller\Profile;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class PersonalInfo extends AbstractController
{
    #[Route('/profile/personal-info', name: 'app_profile_personal_info', methods: ['GET', 'POST'])]
    public function __invoke(): Response
    {
        $user = $this->getUser();

        // TODO: Implement personal information form
        return $this->render('@User/profile/personal_info.html.twig');
    }
}
