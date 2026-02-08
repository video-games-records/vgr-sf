<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
#[IsGranted('ROLE_USER')]
class FriendController extends AbstractLocalizedController
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/friends', name: 'vgr_friends', methods: ['GET'])]
    public function list(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $player = $this->playerRepository->getPlayerFromUser($user);

        if ($player === null) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('@VideoGamesRecordsCore/player/friends.html.twig', [
            'player' => $player,
            'friends' => $player->getFriends(),
        ]);
    }

    #[Route('/friends/add', name: 'vgr_friends_add', methods: ['POST'])]
    public function add(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('add-friend', $request->request->getString('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token');
            return $this->redirectToRoute('vgr_friends');
        }

        /** @var User $user */
        $user = $this->getUser();
        $player = $this->playerRepository->getPlayerFromUser($user);

        if ($player === null) {
            throw $this->createAccessDeniedException();
        }

        $friendId = $request->request->getInt('player');
        if ($friendId === 0) {
            $this->addFlash('error', $this->translator->trans('friend.add.error.not_found', [], 'VgrCore'));
            return $this->redirectToRoute('vgr_friends');
        }

        if ($friendId === $player->getId()) {
            $this->addFlash('error', $this->translator->trans('friend.add.error.self', [], 'VgrCore'));
            return $this->redirectToRoute('vgr_friends');
        }

        $friend = $this->playerRepository->find($friendId);
        if ($friend === null) {
            $this->addFlash('error', $this->translator->trans('friend.add.error.not_found', [], 'VgrCore'));
            return $this->redirectToRoute('vgr_friends');
        }

        if ($player->getFriends()->contains($friend)) {
            $this->addFlash('warning', $this->translator->trans('friend.add.error.already_friend', [], 'VgrCore'));
            return $this->redirectToRoute('vgr_friends');
        }

        $player->addFriend($friend);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('friend.add.success', [], 'VgrCore'));

        return $this->redirectToRoute('vgr_friends');
    }

    #[Route('/friends/{id}/remove', name: 'vgr_friends_remove', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function remove(Request $request, int $id): Response
    {
        if (!$this->isCsrfTokenValid('remove-friend-' . $id, $request->request->getString('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token');
            return $this->redirectToRoute('vgr_friends');
        }

        /** @var User $user */
        $user = $this->getUser();
        $player = $this->playerRepository->getPlayerFromUser($user);

        if ($player === null) {
            throw $this->createAccessDeniedException();
        }

        $friend = $this->playerRepository->find($id);
        if ($friend === null) {
            $this->addFlash('error', $this->translator->trans('friend.add.error.not_found', [], 'VgrCore'));
            return $this->redirectToRoute('vgr_friends');
        }

        $player->removeFriend($friend);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('friend.remove.success', [], 'VgrCore'));

        return $this->redirectToRoute('vgr_friends');
    }
}
