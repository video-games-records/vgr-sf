<?php

declare(strict_types=1);

namespace App\SharedKernel\Presentation\Web\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LocaleSwitcherController extends AbstractController
{
    #[Route('/admin/locale/{locale}', name: 'admin_locale_switch', methods: ['GET'])]
    public function switchLocale(string $locale, Request $request): Response
    {
        // Valider la locale
        $supportedLocales = ['en', 'fr', 'de', 'it', 'ja', 'es', 'pt_BR', 'zh_CN'];

        if (!in_array($locale, $supportedLocales)) {
            $locale = 'en';
        }

        // Stocker la locale en session
        $request->getSession()->set('_locale', $locale);

        // Rediriger vers la page précédente ou vers l'admin
        $referer = $request->headers->get('referer');
        if ($referer && str_contains($referer, '/admin')) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('sonata_admin_dashboard');
    }
}
