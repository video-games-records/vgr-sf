<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\GamingPlatform\Presentation\Web\Controller;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Application\Service\LinkPlatformService;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformIdentity;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Infrastructure\Provider\RetroAchievementsProvider;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class RetroAchievementsLinkController extends AbstractLocalizedController
{
    public function __construct(
        private readonly Security $security,
        private readonly PlayerRepository $playerRepository,
        private readonly RetroAchievementsProvider $provider,
        private readonly LinkPlatformService $linkPlatformService,
    ) {
    }

    #[Route('/platform/retro-achievements/link', name: 'vgr_gaming_platform_ra_link', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('ra_link', $request->request->getString('_token'))) {
            $this->addFlash('danger', 'platform.connection.error');

            return $this->redirectToRoute('app_profile_platforms');
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $player = $this->playerRepository->getPlayerFromUser($user);

        if ($player === null) {
            throw new NotFoundHttpException('Player not found.');
        }

        $username = trim($request->request->getString('username'));

        if ($username === '') {
            $this->addFlash('danger', 'platform.ra.username_required');

            return $this->redirectToRoute('app_profile_platforms');
        }

        try {
            $profile = $this->provider->fetchProfile($username);
        } catch (\RuntimeException) {
            $this->addFlash('danger', 'platform.ra.username_not_found');

            return $this->redirectToRoute('app_profile_platforms');
        }

        $identity = new PlatformIdentity(
            externalId: $profile->username,
            username: $profile->username,
        );

        $this->linkPlatformService->link($player, PlatformEnum::RETRO_ACHIEVEMENTS, $identity);

        $this->addFlash('success', 'platform.connection.success');

        return $this->redirectToRoute('app_profile_platforms');
    }
}
