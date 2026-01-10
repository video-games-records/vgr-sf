<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * LocaleSubscriber automatically manages locale detection and persistence.
 *
 * This subscriber:
 * - Detects the locale from the URL parameter (_locale)
 * - Stores it in the session for persistence
 * - Sets it as the default locale for the current request
 * - Ensures URL generation automatically includes the correct locale
 *
 * With this subscriber, you don't need to manually pass the locale parameter when generating URLs:
 * - In Twig: {{ path('static_faq') }} will automatically become /en/faq or /fr/faq
 * - In Controllers: $this->generateUrl('static_faq') works the same way
 *
 * @see https://symfony.com/doc/current/session/locale_sticky_session.html
 */
class LocaleSubscriber implements EventSubscriberInterface
{
    private string $defaultLocale;

    public function __construct(string $defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Skip if not a master request
        if (!$event->isMainRequest()) {
            return;
        }

        // Try to see if the locale has been set as a _locale routing parameter
        if ($locale = $request->attributes->get('_locale')) {
            $request->setLocale($locale);
            // Store locale in session for future requests
            $request->getSession()->set('_locale', $locale);
        } else {
            // If no explicit locale has been set on this request, use one from the session or default
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Must be registered before the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
