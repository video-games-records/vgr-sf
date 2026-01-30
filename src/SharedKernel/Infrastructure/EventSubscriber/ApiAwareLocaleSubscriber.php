<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Decorator for Sonata's LocaleSubscriber that skips API routes.
 */
final class ApiAwareLocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(private string $defaultLocale = 'en')
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Skip stateless API requests entirely
        if (str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        if (!$request->hasPreviousSession()) {
            return;
        }

        // try to see if the locale has been set as a _locale routing parameter
        if ($request->attributes->has('_locale')) {
            $locale = (string) $request->attributes->get('_locale');
            $request->getSession()->set('_locale', $locale);

            return;
        }

        // if no explicit locale has been set on this request, use one from the session
        $request->setLocale((string) $request->getSession()->get('_locale', $this->defaultLocale));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
