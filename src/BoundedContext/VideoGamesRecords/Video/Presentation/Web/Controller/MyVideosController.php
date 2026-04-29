<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Video\Presentation\Web\Controller;

use App\BoundedContext\VideoGamesRecords\Video\Domain\Entity\Video;
use App\BoundedContext\VideoGamesRecords\Video\Infrastructure\Doctrine\Repository\VideoRepository;
use App\BoundedContext\VideoGamesRecords\Video\Presentation\Form\Type\VideoEditType;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
#[IsGranted('ROLE_USER')]
class MyVideosController extends AbstractLocalizedController
{
    private const int VIDEOS_PER_PAGE = 12;

    public function __construct(
        private readonly VideoRepository $videoRepository,
        private readonly PlayerRepository $playerRepository,
        private readonly GameRepository $gameRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/my-videos', name: 'vgr_my_videos')]
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var Player $player */
        $player = $this->playerRepository->getPlayerFromUser($user);

        $page = max(1, (int) $request->query->get('page', 1));
        $gameId = $request->query->get('game');

        // Build query
        $qb = $this->videoRepository
            ->createQueryBuilder('v')
            ->select('v, p, g')
            ->leftJoin('v.player', 'p')
            ->leftJoin('v.game', 'g')
            ->where('v.player = :player')
            ->setParameter('player', $player)
            ->orderBy('v.createdAt', 'DESC');

        // Filter by game if provided
        $selectedGame = null;
        if ($gameId) {
            $selectedGame = $this->gameRepository->find($gameId);
            if ($selectedGame) {
                $qb->andWhere('v.game = :game')
                   ->setParameter('game', $selectedGame);
            }
        }

        // Pagination
        $paginator = new Paginator($qb);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * self::VIDEOS_PER_PAGE)
            ->setMaxResults(self::VIDEOS_PER_PAGE);

        $totalVideos = count($paginator);
        $totalPages = max(1, (int) ceil($totalVideos / self::VIDEOS_PER_PAGE));

        // Get player's games for filter dropdown (all videos, including inactive)
        $playerGames = $this->gameRepository
            ->createQueryBuilder('g')
            ->select('g')
            ->where('EXISTS (SELECT v FROM App\BoundedContext\VideoGamesRecords\Video\Domain\Entity\Video v WHERE v.game = g AND v.player = :player)')
            ->setParameter('player', $player)
            ->orderBy('g.libGameEn', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('@VideoGamesRecordsVideo/my-videos/index.html.twig', [
            'videos' => $paginator,
            'player' => $player,
            'total_videos' => $totalVideos,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'player_games' => $playerGames,
            'selected_game' => $selectedGame,
        ]);
    }

    #[Route('/my-videos/{id}/edit', name: 'vgr_my_videos_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var Player $player */
        $player = $this->playerRepository->getPlayerFromUser($user);

        $video = $this->videoRepository->find($id);

        if (!$video || $video->getPlayer()->getId() !== $player->getId()) {
            throw $this->createNotFoundException('Video not found or access denied');
        }

        $form = $this->createForm(VideoEditType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($video);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('my_videos.edit.success', [], 'VgrVideo'));

            return $this->redirectToRoute('vgr_my_videos');
        }

        return $this->render('@VideoGamesRecordsVideo/my-videos/edit.html.twig', [
            'form' => $form->createView(),
            'video' => $video
        ]);
    }
}
