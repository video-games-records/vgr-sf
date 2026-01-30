<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Disables Sonata's LocaleSubscriber which uses session on stateless API requests.
 */
final class DisableSonataLocaleSubscriberPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $serviceId = 'sonata_translation.locale_switcher.locale_subscriber';

        if (!$container->hasDefinition($serviceId)) {
            return;
        }

        $definition = $container->getDefinition($serviceId);
        $definition->clearTags();
    }
}