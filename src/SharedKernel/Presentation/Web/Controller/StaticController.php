<?php

declare(strict_types=1);

namespace App\SharedKernel\Presentation\Web\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class StaticController extends AbstractLocalizedController
{
    #[Route('/faq', name: 'static_faq')]
    public function faq(string $_locale): Response
    {
        return $this->render(sprintf('@SharedKernel/static/faq.%s.html.twig', $_locale));
    }

    #[Route('/rules', name: 'static_rules')]
    public function rules(string $_locale): Response
    {
        return $this->render(sprintf('@SharedKernel/static/rules.%s.html.twig', $_locale));
    }

    #[Route('/about', name: 'static_about')]
    public function about(string $_locale): Response
    {
        return $this->render(sprintf('@SharedKernel/static/about.%s.html.twig', $_locale));
    }

    #[Route('/privacy', name: 'static_privacy')]
    public function privacy(string $_locale): Response
    {
        return $this->render(sprintf('@SharedKernel/static/privacy.%s.html.twig', $_locale));
    }

    #[Route('/terms', name: 'static_terms')]
    public function terms(string $_locale): Response
    {
        return $this->render(sprintf('@SharedKernel/static/terms.%s.html.twig', $_locale));
    }

    #[Route('/contact', name: 'static_contact')]
    public function contact(string $_locale): Response
    {
        return $this->render(sprintf('@SharedKernel/static/contact.%s.html.twig', $_locale));
    }
}
