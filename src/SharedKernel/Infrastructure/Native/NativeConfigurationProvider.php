<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\Native;

use Symfony\UX\Native\Attribute\AsNativeConfiguration;
use Symfony\UX\Native\Attribute\AsNativeConfigurationProvider;
use Symfony\UX\Native\Configuration\Configuration;
use Symfony\UX\Native\Configuration\Rule;

#[AsNativeConfigurationProvider]
class NativeConfigurationProvider
{
    #[AsNativeConfiguration('/config/native.json')]
    public function configuration(): Configuration
    {
        return new Configuration(
            settings: [
                'screenshots' => 'enabled',
            ],
            rules: [
                // Auth pages → modal (pas de navigation push)
                new Rule(
                    patterns: [
                        '/[a-z_]+/login$',
                        '/[a-z_]+/register$',
                        '/[a-z_]+/reset-password/.*',
                    ],
                    properties: [
                        'context' => 'modal',
                        'pullToRefresh' => false,
                    ]
                ),

                // Pages de profil personnel → modal
                new Rule(
                    patterns: [
                        '/[a-z_]+/profile.*',
                    ],
                    properties: [
                        'context' => 'modal',
                        'pullToRefresh' => true,
                    ]
                ),

                // Soumission de scores → modal
                new Rule(
                    patterns: [
                        '/[a-z_]+/game/.+/scores$',
                        '/[a-z_]+/game/.+/group/.+/scores$',
                        '/[a-z_]+/game/.+/group/.+/chart/.+/score$',
                    ],
                    properties: [
                        'context' => 'modal',
                        'pullToRefresh' => false,
                    ]
                ),

                // Messagerie → modal
                new Rule(
                    patterns: [
                        '/[a-z_]+/messages/compose$',
                    ],
                    properties: [
                        'context' => 'modal',
                        'pullToRefresh' => false,
                    ]
                ),

                // Pages exclues (admin, health, api)
                new Rule(
                    patterns: [
                        '^/admin/.*',
                        '^/health.*',
                        '^/api/.*',
                    ],
                    properties: [
                        'presentation' => 'none',
                    ]
                ),

                // Toutes les autres pages → push navigation standard
                new Rule(
                    patterns: [
                        '/.*',
                    ],
                    properties: [
                        'context' => 'default',
                        'pullToRefresh' => true,
                    ]
                ),
            ]
        );
    }
}
