<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Presentation\Web\Controller\Team;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamRequest;
use App\BoundedContext\VideoGamesRecords\Team\Domain\ValueObject\TeamRequestStatus;
use App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\Repository\TeamRequestRepository;
use App\BoundedContext\VideoGamesRecords\Team\Presentation\Form\CreateTeamFormType;
use App\BoundedContext\VideoGamesRecords\Team\Presentation\Form\EditTeamFormType;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
#[IsGranted('ROLE_USER')]
class TeamProfileController extends AbstractLocalizedController
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly TeamRequestRepository $teamRequestRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/team/manage', name: 'vgr_team_manage', methods: ['GET', 'POST'])]
    public function manage(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $player = $this->playerRepository->getPlayerFromUser($user);

        if ($player === null) {
            throw $this->createAccessDeniedException();
        }

        $team = $player->getTeam();

        // Scenario 2 - Leader: edit team + manage requests
        if ($team !== null && $player->isLeader()) {
            $form = $this->createForm(EditTeamFormType::class, $team);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->entityManager->flush();
                $this->addFlash('success', $this->translator->trans('team.manage.edit.success', [], 'VgrTeam'));

                return $this->redirectToRoute('vgr_team_manage');
            }

            $requests = $this->teamRequestRepository->findActiveByTeam($team);

            return $this->render('@VideoGamesRecordsTeam/team/manage.html.twig', [
                'scenario' => 'leader',
                'player' => $player,
                'team' => $team,
                'form' => $form,
                'requests' => $requests,
            ]);
        }

        // Scenario 3 - Member: view team info + leave button
        if ($team !== null) {
            return $this->render('@VideoGamesRecordsTeam/team/manage.html.twig', [
                'scenario' => 'member',
                'player' => $player,
                'team' => $team,
            ]);
        }

        // Scenario 1 & 4 - No team: create form + show sent requests
        $newTeam = new Team();
        $form = $this->createForm(CreateTeamFormType::class, $newTeam);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newTeam->setLeader($player);
            $player->setTeam($newTeam);
            $this->entityManager->persist($newTeam);
            $this->entityManager->flush();

            $this->teamRequestRepository->cancelActiveRequestsForPlayer($player);

            $this->addFlash('success', $this->translator->trans('team.manage.create.success', [], 'VgrTeam'));

            return $this->redirectToRoute('vgr_team_manage');
        }

        $playerRequests = $this->teamRequestRepository->findByPlayer($player);

        return $this->render('@VideoGamesRecordsTeam/team/manage.html.twig', [
            'scenario' => 'no_team',
            'player' => $player,
            'form' => $form,
            'playerRequests' => $playerRequests,
        ]);
    }

    #[Route('/team/manage/leave', name: 'vgr_team_leave', methods: ['POST'])]
    public function leave(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('team-leave', $request->request->getString('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token');
            return $this->redirectToRoute('vgr_team_manage');
        }

        /** @var User $user */
        $user = $this->getUser();
        $player = $this->playerRepository->getPlayerFromUser($user);

        if ($player === null || $player->getTeam() === null) {
            return $this->redirectToRoute('vgr_team_manage');
        }

        if ($player->isLeader()) {
            $this->addFlash('error', $this->translator->trans('team.manage.member.leave_error_leader', [], 'VgrTeam'));
            return $this->redirectToRoute('vgr_team_manage');
        }

        $player->setTeam(null);
        $this->entityManager->flush();
        $this->addFlash('success', $this->translator->trans('team.manage.member.leave_success', [], 'VgrTeam'));

        return $this->redirectToRoute('vgr_team_manage');
    }

    #[Route('/team/manage/request/{id}/accept', name: 'vgr_team_request_accept', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function acceptRequest(Request $request, int $id): Response
    {
        if (!$this->isCsrfTokenValid('team-request-accept-' . $id, $request->request->getString('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token');
            return $this->redirectToRoute('vgr_team_manage');
        }

        /** @var User $user */
        $user = $this->getUser();
        $player = $this->playerRepository->getPlayerFromUser($user);

        if ($player === null) {
            throw $this->createAccessDeniedException();
        }

        $teamRequest = $this->teamRequestRepository->find($id);
        if (!$teamRequest instanceof TeamRequest) {
            return $this->redirectToRoute('vgr_team_manage');
        }

        // Verify the current player is the leader of the requested team
        if ($player->getTeam() === null || $teamRequest->getTeam()->getId() !== $player->getTeam()->getId() || !$player->isLeader()) {
            $this->addFlash('error', $this->translator->trans('team.manage.requests.error_not_leader', [], 'VgrTeam'));
            return $this->redirectToRoute('vgr_team_manage');
        }

        if (!$teamRequest->getTeamRequestStatus()->isActive()) {
            $this->addFlash('error', $this->translator->trans('team.manage.requests.error_not_active', [], 'VgrTeam'));
            return $this->redirectToRoute('vgr_team_manage');
        }

        // Check that the requesting player doesn't already have a team
        if ($teamRequest->getPlayer()->getTeam() !== null) {
            $this->addFlash('error', $this->translator->trans('team.manage.requests.accept_error_has_team', [], 'VgrTeam'));
            return $this->redirectToRoute('vgr_team_manage');
        }

        // 1. Accept the request => triggers TeamRequestListener (sets player.team)
        $teamRequest->setStatus(TeamRequestStatus::ACCEPTED);
        $this->entityManager->flush();

        // 2. Cancel all other active requests for this player
        $this->teamRequestRepository->cancelActiveRequestsForPlayer($teamRequest->getPlayer());

        $this->addFlash('success', $this->translator->trans('team.manage.requests.accept_success', [], 'VgrTeam'));

        return $this->redirectToRoute('vgr_team_manage');
    }

    #[Route('/team/manage/request/{id}/refuse', name: 'vgr_team_request_refuse', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function refuseRequest(Request $request, int $id): Response
    {
        if (!$this->isCsrfTokenValid('team-request-refuse-' . $id, $request->request->getString('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token');
            return $this->redirectToRoute('vgr_team_manage');
        }

        /** @var User $user */
        $user = $this->getUser();
        $player = $this->playerRepository->getPlayerFromUser($user);

        if ($player === null) {
            throw $this->createAccessDeniedException();
        }

        $teamRequest = $this->teamRequestRepository->find($id);
        if (!$teamRequest instanceof TeamRequest) {
            return $this->redirectToRoute('vgr_team_manage');
        }

        if ($player->getTeam() === null || $teamRequest->getTeam()->getId() !== $player->getTeam()->getId() || !$player->isLeader()) {
            $this->addFlash('error', $this->translator->trans('team.manage.requests.error_not_leader', [], 'VgrTeam'));
            return $this->redirectToRoute('vgr_team_manage');
        }

        if (!$teamRequest->getTeamRequestStatus()->isActive()) {
            $this->addFlash('error', $this->translator->trans('team.manage.requests.error_not_active', [], 'VgrTeam'));
            return $this->redirectToRoute('vgr_team_manage');
        }

        $teamRequest->setStatus(TeamRequestStatus::REFUSED);
        $this->entityManager->flush();
        $this->addFlash('success', $this->translator->trans('team.manage.requests.refuse_success', [], 'VgrTeam'));

        return $this->redirectToRoute('vgr_team_manage');
    }

    #[Route('/team/manage/request/{id}/cancel', name: 'vgr_team_request_cancel', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function cancelRequest(Request $request, int $id): Response
    {
        if (!$this->isCsrfTokenValid('team-request-cancel-' . $id, $request->request->getString('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token');
            return $this->redirectToRoute('vgr_team_manage');
        }

        /** @var User $user */
        $user = $this->getUser();
        $player = $this->playerRepository->getPlayerFromUser($user);

        if ($player === null) {
            throw $this->createAccessDeniedException();
        }

        $teamRequest = $this->teamRequestRepository->find($id);
        if (!$teamRequest instanceof TeamRequest) {
            return $this->redirectToRoute('vgr_team_manage');
        }

        if ($teamRequest->getPlayer()->getId() !== $player->getId()) {
            $this->addFlash('error', $this->translator->trans('team.manage.requests.error_not_yours', [], 'VgrTeam'));
            return $this->redirectToRoute('vgr_team_manage');
        }

        if (!$teamRequest->getTeamRequestStatus()->isActive()) {
            $this->addFlash('error', $this->translator->trans('team.manage.requests.error_not_active', [], 'VgrTeam'));
            return $this->redirectToRoute('vgr_team_manage');
        }

        $teamRequest->setStatus(TeamRequestStatus::CANCELED);
        $this->entityManager->flush();
        $this->addFlash('success', $this->translator->trans('team.manage.requests.cancel_success', [], 'VgrTeam'));

        return $this->redirectToRoute('vgr_team_manage');
    }
}
