<?php

declare(strict_types=1);

namespace App\SharedKernel\Presentation\Web\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class StaticController extends AbstractLocalizedController
{
    public function __construct(private readonly Environment $twig)
    {
    }

    private function resolveTemplate(string $name, string $locale): string
    {
        $template = sprintf('@SharedKernel/static/%s.%s.html.twig', $name, $locale);
        if ($locale !== 'en' && !$this->twig->getLoader()->exists($template)) {
            return sprintf('@SharedKernel/static/%s.en.html.twig', $name);
        }

        return $template;
    }

    #[Route('/faq', name: 'static_faq')]
    public function faq(string $_locale): Response
    {
        return $this->render($this->resolveTemplate('faq', $_locale));
    }

    #[Route('/rules', name: 'static_rules')]
    public function rules(string $_locale): Response
    {
        return $this->render($this->resolveTemplate('rules', $_locale));
    }

    #[Route('/about', name: 'static_about')]
    public function about(string $_locale): Response
    {
        return $this->render($this->resolveTemplate('about', $_locale));
    }

    #[Route('/privacy', name: 'static_privacy')]
    public function privacy(string $_locale): Response
    {
        return $this->render($this->resolveTemplate('privacy', $_locale));
    }

    #[Route('/terms', name: 'static_terms')]
    public function terms(string $_locale): Response
    {
        return $this->render($this->resolveTemplate('terms', $_locale));
    }

    #[Route('/contact', name: 'static_contact')]
    public function contact(string $_locale): Response
    {
        return $this->render($this->resolveTemplate('contact', $_locale));
    }
}
