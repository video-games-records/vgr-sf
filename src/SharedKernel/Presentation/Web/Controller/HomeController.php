<?php

declare(strict_types=1);

namespace App\SharedKernel\Presentation\Web\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class HomeController extends AbstractLocalizedController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('@SharedKernel/home.html.twig');
    }
}
