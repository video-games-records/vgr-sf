<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Web\Controller;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\UX\Native\EventListener\NativeListener;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class SecurityController extends AbstractLocalizedController
{
    public function __construct(private readonly PlayerRepository $playerRepository)
    {
    }

    #[Route('/login', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            if ($request->attributes->get(NativeListener::NATIVE_ATTRIBUTE, false)) {
                /** @var User $user */
                $user = $this->getUser();
                $player = $this->playerRepository->getPlayerFromUser($user);
                if ($player !== null) {
                    return $this->redirectToRoute('vgr_player_profile_overview', [
                        '_locale' => $request->getLocale(),
                        'id' => $player->getId(),
                        'slug' => $player->getSlug(),
                    ]);
                }
            }

            return $this->redirectToRoute('home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@User/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method can be blank - it will be intercepted by the logout key on your firewall
        throw new \LogicException(
            'This method can be blank - it will be intercepted by the logout key on your firewall.'
        );
    }
}
