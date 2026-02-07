<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Presentation\Web\Controller\Profile;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamRequest;
use App\BoundedContext\VideoGamesRecords\Team\Domain\ValueObject\TeamRequestStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Overview extends AbstractProfileController
{
    #[Route('/team/{id}-{slug}', name: 'vgr_team_profile_overview', requirements: ['id' => '\d+'])]
    public function __invoke(int $id, string $slug): Response
    {
        $team = $this->getTeam($id, $slug);

        return $this->render('@VideoGamesRecordsTeam/profile/overview.html.twig', $this->getBaseParams($team, 'overview'));
    }

    #[Route('/team/{id}-{slug}/join', name: 'vgr_team_profile_join', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function join(
        int $id,
        string $slug,
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
    ): Response {
        $team = $this->getTeam($id, $slug);

        if (!$this->isCsrfTokenValid('team-join-' . $team->getId(), $request->request->getString('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token');
            return $this->redirectToRoute('vgr_team_profile_overview', ['id' => $id, 'slug' => $slug]);
        }

        if (!$this->canJoin($team)) {
            return $this->redirectToRoute('vgr_team_profile_overview', ['id' => $id, 'slug' => $slug]);
        }

        /** @var User $user */
        $user = $this->getUser();
        $player = $this->playerRepository->getPlayerFromUser($user);

        $teamRequest = new TeamRequest();
        $teamRequest->setTeam($team);
        $teamRequest->setPlayer($player);
        $teamRequest->setStatus(TeamRequestStatus::ACTIVE);

        $entityManager->persist($teamRequest);
        $entityManager->flush();

        $this->addFlash('success', $translator->trans('team.profile.join_success', [], 'VgrTeam'));

        return $this->redirectToRoute('vgr_team_profile_overview', ['id' => $id, 'slug' => $slug]);
    }
}
