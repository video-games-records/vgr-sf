<?php

declare(strict_types=1);

namespace App\SharedKernel\Presentation\Web\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Root controller that handles the "/" route and redirects to the appropriate locale.
 *
 * This controller is NOT localized (doesn't extend AbstractLocalizedController)
 * because it needs to handle the root "/" route without any locale prefix.
 */
class RootController extends AbstractController
{
    private const AVAILABLE_LOCALES = ['en', 'fr'];
    private const DEFAULT_LOCALE = 'en';

    /**
     * Root route that redirects to the user's preferred locale.
     *
     * Detection order:
     * 1. Locale stored in session (from previous visit)
     * 2. Browser's Accept-Language header
     * 3. Default locale (en)
     */
    #[Route('/', name: 'root')]
    public function index(Request $request): Response
    {
        // 1. Check if user has a preferred locale in session
        $session = $request->getSession();
        if ($session->has('_locale')) {
            $locale = $session->get('_locale');
            if (in_array($locale, self::AVAILABLE_LOCALES, true)) {
                return $this->redirectToRoute('home', ['_locale' => $locale]);
            }
        }

        // 2. Detect locale from browser's Accept-Language header
        $preferredLocale = $request->getPreferredLanguage(self::AVAILABLE_LOCALES);

        // 3. Use detected locale or fallback to default
        $locale = $preferredLocale ?: self::DEFAULT_LOCALE;

        // Store the detected locale in session for next time
        $session->set('_locale', $locale);

        // Redirect to the home page with the detected locale
        return $this->redirectToRoute('home', ['_locale' => $locale]);
    }
}
