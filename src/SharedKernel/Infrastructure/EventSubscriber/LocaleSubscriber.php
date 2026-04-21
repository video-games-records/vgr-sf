<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    private string $defaultLocale;

    public function __construct(string $defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Skip if locale is already set in the route
        if ($request->attributes->has('_locale')) {
            return;
        }

        // Try to get locale from session
        $locale = $request->getSession()->get('_locale');

        // If no locale in session, use the default
        if (!$locale) {
            $locale = $this->defaultLocale;
        }

        // Set the locale for this request
        $request->setLocale($locale);
    }
}
