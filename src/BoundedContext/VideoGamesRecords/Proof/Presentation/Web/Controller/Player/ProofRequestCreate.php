<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Presentation\Web\Controller\Player;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;
use App\BoundedContext\VideoGamesRecords\Proof\Application\DataProvider\CanAskProofProvider;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\ProofRequest;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
#[IsGranted('ROLE_USER')]
class ProofRequestCreate extends AbstractController
{
    public function __construct(
        private readonly PlayerChartRepository $playerChartRepository,
        private readonly UserProvider $userProvider,
        private readonly CanAskProofProvider $canAskProofProvider,
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Route(
        '/player-chart/{id}/request-proof',
        name: 'vgr_proof_request_create',
        methods: ['POST'],
        requirements: ['id' => '\d+']
    )]
    public function __invoke(int $id, Request $request): Response
    {
        $playerChart = $this->playerChartRepository->find($id);

        if (!$playerChart) {
            throw $this->createNotFoundException('Score not found');
        }

        $player = $this->userProvider->getPlayer();

        if ($player === null) {
            throw $this->createAccessDeniedException();
        }

        if ($playerChart->getPlayer()->getId() === $player->getId()) {
            throw $this->createAccessDeniedException();
        }

        if ($playerChart->getStatus() !== PlayerChartStatusEnum::NONE) {
            $this->addFlash('error', $this->translator->trans('proof_request.error.status_not_allowed', [], 'VgrProof'));
            return $this->redirectToPlayerChartShow($playerChart);
        }

        /** @var string|null $token */
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('proof_request_' . $id, $token)) {
            $this->addFlash('error', $this->translator->trans('proof_request.error.invalid_token', [], 'VgrProof'));
            return $this->redirectToPlayerChartShow($playerChart);
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            $canAsk = $this->canAskProofProvider->load($player);
            if (!$canAsk) {
                $this->addFlash('error', $this->translator->trans('proof_request.error.daily_limit', [], 'VgrProof'));
                return $this->redirectToPlayerChartShow($playerChart);
            }
        }

        $proofRequest = new ProofRequest();
        $proofRequest->setPlayerChart($playerChart);
        $proofRequest->setPlayerRequesting($player);
        $proofRequest->setPlayerResponding($playerChart->getPlayer());

        /** @var string|null $message */
        $message = $request->request->get('message');
        if ($message !== null && trim($message) !== '') {
            $proofRequest->setMessage(trim($message));
        }

        $this->em->persist($proofRequest);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('proof_request.success', [], 'VgrProof'));
        return $this->redirectToPlayerChartShow($playerChart);
    }

    private function redirectToPlayerChartShow(PlayerChart $playerChart): Response
    {
        $chart = $playerChart->getChart();
        $group = $chart->getGroup();
        $game = $group->getGame();

        return $this->redirectToRoute('vgr_player_chart_show', [
            'id' => $game->getId(),
            'slug' => $game->getSlug(),
            'groupId' => $group->getId(),
            'groupSlug' => $group->getSlug(),
            'chartId' => $chart->getId(),
            'chartSlug' => $chart->getSlug(),
            'playerChartId' => $playerChart->getId(),
        ]);
    }
}
