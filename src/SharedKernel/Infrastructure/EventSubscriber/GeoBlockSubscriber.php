<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\EventSubscriber;

use App\SharedKernel\Domain\Interface\CountryResolverInterface;
use App\SharedKernel\Infrastructure\GeoIp\GeoChallengeCookieManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GeoBlockSubscriber implements EventSubscriberInterface
{
    /**
     * @param string[] $blockedCountries
     */
    public function __construct(
        private readonly CountryResolverInterface $countryResolver,
        private readonly GeoChallengeCookieManager $cookieManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LoggerInterface $logger,
        #[Autowire('%app.geoip.blocked_countries%')]
        private readonly array $blockedCountries,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 10]],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if (str_starts_with($path, '/api') || str_starts_with($path, '/_')) {
            return;
        }

        if ('app_geo_challenge' === $request->attributes->get('_route')) {
            return;
        }

        if ($this->cookieManager->isPassed($request)) {
            return;
        }

        $country = $this->countryResolver->resolveCountry((string) $request->getClientIp());

        if (null === $country || !in_array($country, $this->blockedCountries, true)) {
            return;
        }

        $this->logger->info('GeoIP challenge triggered', [
            'ip' => $request->getClientIp(),
            'country' => $country,
            'path' => $path,
        ]);

        $challengeUrl = $this->urlGenerator->generate('app_geo_challenge', [
            '_locale' => $request->getLocale(),
            'redirect' => $request->getRequestUri(),
        ]);

        $event->setResponse(new RedirectResponse($challengeUrl));
    }
}
