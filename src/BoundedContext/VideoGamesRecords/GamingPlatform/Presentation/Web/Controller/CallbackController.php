<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\GamingPlatform\Presentation\Web\Controller;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Application\Service\LinkPlatformService;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Application\Service\PlatformProviderRegistry;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class CallbackController extends AbstractLocalizedController
{
    public function __construct(
        private readonly Security $security,
        private readonly PlayerRepository $playerRepository,
        private readonly PlatformProviderRegistry $providerRegistry,
        private readonly LinkPlatformService $linkPlatformService,
    ) {
    }

    #[Route('/platform/{platform}/callback', name: 'vgr_gaming_platform_callback')]
    public function __invoke(string $platform, Request $request): Response
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $player = $this->playerRepository->getPlayerFromUser($user);

        if ($player === null) {
            throw new NotFoundHttpException('Player not found.');
        }

        try {
            $platformEnum = PlatformEnum::from($platform);
            $provider = $this->providerRegistry->get($platformEnum);
            $identity = $provider->handleCallback($request);
        } catch (\RuntimeException) {
            $this->addFlash('danger', 'platform.connection.error');

            return $this->redirectToRoute('app_profile_platforms');
        }

        $this->linkPlatformService->link($player, $platformEnum, $identity);

        $this->addFlash('success', 'platform.connection.success');

        return $this->redirectToRoute('app_profile_platforms');
    }
}
