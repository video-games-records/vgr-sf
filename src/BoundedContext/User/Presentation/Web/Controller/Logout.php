<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Web\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class Logout extends AbstractController
{
    #[Route('/logout', name: 'app_logout')]
    public function __invoke(): void
    {
        // This method can be blank - it will be intercepted by the logout key on your firewall
        throw new \LogicException(
            'This method can be blank - it will be intercepted by the logout key on your firewall.'
        );
    }
}
