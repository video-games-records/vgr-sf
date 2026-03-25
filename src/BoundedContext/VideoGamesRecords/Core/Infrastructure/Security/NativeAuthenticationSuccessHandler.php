<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\UX\Native\EventListener\NativeListener;

class NativeAuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        HttpUtils $httpUtils,
        private readonly PlayerRepository $playerRepository,
        private readonly RouterInterface $router,
        array $options = [],
    ) {
        parent::__construct($httpUtils, $options);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        if (!$request->attributes->get(NativeListener::NATIVE_ATTRIBUTE, false)) {
            return parent::onAuthenticationSuccess($request, $token) ?? new RedirectResponse('/');
        }

        /** @var User $user */
        $user = $token->getUser();
        $player = $this->playerRepository->getPlayerFromUser($user);

        if ($player === null) {
            return new RedirectResponse($this->router->generate('home', [
                '_locale' => $request->getLocale(),
            ]));
        }

        return new RedirectResponse($this->router->generate('vgr_player_profile_overview', [
            '_locale' => $request->getLocale(),
            'id' => $player->getId(),
            'slug' => $player->getSlug(),
        ]));
    }
}
