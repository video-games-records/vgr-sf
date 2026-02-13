<?php

declare(strict_types=1);

namespace App\SharedKernel\Presentation\Web\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class SearchController extends AbstractLocalizedController
{
    #[Route('/search', name: 'global_search')]
    public function index(): Response
    {
        return $this->render('@SharedKernel/search/index.html.twig');
    }
}
