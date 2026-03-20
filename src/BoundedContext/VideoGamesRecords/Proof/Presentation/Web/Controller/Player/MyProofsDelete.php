<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Presentation\Web\Controller\Player;

use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\ProofStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class MyProofsDelete extends AbstractController
{
    public function __construct(
        private readonly UserProvider $userProvider,
        private readonly EntityManagerInterface $em,
        private readonly PlayerChartRepository $playerChartRepository
    ) {
    }

    #[Route('/my-proofs/delete/{id}', name: 'vgr_my_proofs_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function __invoke(int $id, Request $request): Response
    {
        $playerChart = $this->playerChartRepository->find($id);

        if (!$playerChart) {
            throw $this->createNotFoundException('Score not found');
        }

        $player = $this->userProvider->getPlayer();

        if ($player === null || $playerChart->getPlayer()->getId() !== $player->getId()) {
            throw $this->createAccessDeniedException();
        }

        $isAjax = $request->isXmlHttpRequest();

        if ($playerChart->getStatus() !== PlayerChartStatusEnum::PROOF_SENT) {
            if ($isAjax) {
                return new JsonResponse(['error' => 'my_proofs.delete_not_allowed'], Response::HTTP_FORBIDDEN);
            }
            $this->addFlash('error', 'my_proofs.delete_not_allowed');
            return $this->redirectToGame($playerChart);
        }

        /** @var string|null $token */
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('my_proofs_delete_' . $id, $token)) {
            if ($isAjax) {
                return new JsonResponse(['error' => 'my_proofs.delete_error'], Response::HTTP_BAD_REQUEST);
            }
            $this->addFlash('error', 'my_proofs.delete_error');
            return $this->redirectToGame($playerChart);
        }

        $proof = $playerChart->getProof();

        $playerChart->setProof(null);
        $playerChart->setStatus(PlayerChartStatusEnum::NONE);

        if ($proof !== null) {
            $proof->setStatus(ProofStatus::DELETED);
        }

        $this->em->flush();

        if ($isAjax) {
            return new Response(
                $this->renderView('@VideoGamesRecordsProof/player/_score_card.html.twig', [
                    'pc' => $playerChart,
                ])
            );
        }

        $this->addFlash('success', 'my_proofs.delete_success');

        return $this->redirectToGame($playerChart);
    }

    private function redirectToGame(PlayerChart $playerChart): Response
    {
        $game = $playerChart->getChart()->getGroup()->getGame();

        return $this->redirectToRoute('vgr_my_proofs_game', [
            'gameId' => $game->getId(),
            'gameSlug' => $game->getSlug(),
        ]);
    }
}
