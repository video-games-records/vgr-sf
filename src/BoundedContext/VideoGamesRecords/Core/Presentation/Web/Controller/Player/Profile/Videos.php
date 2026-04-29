<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player\Profile;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\Video\Application\Service\VideoListService;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class Videos extends AbstractLocalizedController
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly VideoListService $videoListService
    ) {
    }

    #[Route('/player/{id}-{slug}/videos', name: 'vgr_player_profile_videos', requirements: ['id' => '\d+'])]
    public function __invoke(int $id, string $slug, Request $request): Response
    {
        $player = $this->playerRepository->find($id);
        if (!$player) {
            throw new NotFoundHttpException('Player not found');
        }

        $page = max(1, (int) $request->query->get('page', 1));

        // Si c'est une requête AJAX, retourner du JSON
        if ($request->isXmlHttpRequest()) {
            $data = $this->videoListService->getVideosAsJson($page, $request->getLocale(), $player);
            return new JsonResponse($data);
        }

        // Pour la première page (non AJAX), afficher le template complet
        $result = $this->videoListService->getVideosPaginated($page, $player);

        return $this->render('@VideoGamesRecordsCore/player/profile/videos.html.twig', [
            'player' => $player,
            'videos' => $result['videos'],
            'total_videos' => $result['total_videos'],
            'has_more' => $result['has_more'],
            'api_url' => $this->generateUrl('vgr_player_profile_videos', [
                'id' => $player->getId(),
                'slug' => $player->getSlug()
            ]),
        ]);
    }
}
