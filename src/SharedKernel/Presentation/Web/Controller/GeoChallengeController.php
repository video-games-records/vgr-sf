<?php

declare(strict_types=1);

namespace App\SharedKernel\Presentation\Web\Controller;

use App\SharedKernel\Infrastructure\GeoIp\GeoChallengeCookieManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class GeoChallengeController extends AbstractLocalizedController
{
    public function __construct(
        private readonly GeoChallengeCookieManager $cookieManager,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    #[Route('/geo-challenge', name: 'app_geo_challenge', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $redirect = $this->sanitizeRedirect($request->query->get('redirect'));

        if ($request->isMethod('POST')) {
            $token = new CsrfToken('geo_challenge', (string) $request->request->get('_token'));

            if (!$this->csrfTokenManager->isTokenValid($token)) {
                return $this->redirectToRoute('app_geo_challenge', [
                    '_locale' => $request->getLocale(),
                    'redirect' => $redirect,
                ]);
            }

            $response = new RedirectResponse($redirect);
            $response->headers->setCookie($this->cookieManager->createCookie());

            return $response;
        }

        return $this->render('@SharedKernel/geo_challenge.html.twig', [
            'redirect' => $redirect,
        ]);
    }

    private function sanitizeRedirect(?string $redirect): string
    {
        if (null === $redirect || '' === $redirect) {
            return $this->generateUrl('home');
        }

        if (!str_starts_with($redirect, '/') || str_starts_with($redirect, '//') || str_contains($redirect, '://')) {
            return $this->generateUrl('home');
        }

        return $redirect;
    }
}
