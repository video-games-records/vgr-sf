<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\GamingPlatform\Presentation\Web\Controller;

use App\BoundedContext\VideoGamesRecords\GamingPlatform\Application\Service\PlatformProviderRegistry;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class ConnectController extends AbstractLocalizedController
{
    public function __construct(
        private readonly PlatformProviderRegistry $providerRegistry,
    ) {
    }

    #[Route('/platform/{platform}/connect', name: 'vgr_gaming_platform_connect')]
    public function __invoke(string $platform, Request $request): Response
    {
        try {
            $platformEnum = PlatformEnum::from($platform);
        } catch (\ValueError) {
            throw new NotFoundHttpException(sprintf('Unknown platform "%s".', $platform));
        }

        if (!$platformEnum->isSupported()) {
            throw new NotFoundHttpException(sprintf('Platform "%s" is not yet supported.', $platform));
        }

        $callbackUrl = $this->generateUrl(
            'vgr_gaming_platform_callback',
            ['platform' => $platform],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $provider = $this->providerRegistry->get($platformEnum);

        return $this->redirect($provider->getAuthUrl($callbackUrl));
    }
}
