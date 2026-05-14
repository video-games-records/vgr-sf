<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\GamingPlatform\Presentation\Web\Controller;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Application\Service\UnlinkPlatformService;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class DisconnectController extends AbstractLocalizedController
{
    public function __construct(
        private readonly Security $security,
        private readonly PlayerRepository $playerRepository,
        private readonly UnlinkPlatformService $unlinkPlatformService,
    ) {
    }

    #[Route('/platform/{platform}/disconnect', name: 'vgr_gaming_platform_disconnect', methods: ['POST'])]
    public function __invoke(string $platform, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('platform_disconnect_' . $platform, $request->request->getString('_token'))) {
            throw new AccessDeniedException('Invalid CSRF token.');
        }

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
        } catch (\ValueError) {
            throw new NotFoundHttpException(sprintf('Unknown platform "%s".', $platform));
        }

        $this->unlinkPlatformService->unlink($player, $platformEnum);

        $this->addFlash('success', 'platform.disconnection.success');

        return $this->redirectToRoute('app_profile_platforms');
    }
}
