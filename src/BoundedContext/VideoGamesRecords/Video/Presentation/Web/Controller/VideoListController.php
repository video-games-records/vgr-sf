<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Video\Presentation\Web\Controller;

use App\BoundedContext\VideoGamesRecords\Video\Application\Service\VideoListService;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class VideoListController extends AbstractLocalizedController
{
    public function __construct(
        private readonly VideoListService $videoListService
    ) {
    }

    #[Route('/videos', name: 'vgr_video_list')]
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));

        // Si c'est une requête AJAX, retourner du JSON
        if ($request->isXmlHttpRequest()) {
            $data = $this->videoListService->getVideosAsJson($page, $request->getLocale());
            return new JsonResponse($data);
        }

        // Pour la première page (non AJAX), afficher le template complet
        $result = $this->videoListService->getVideosPaginated($page);

        return $this->render('@VideoGamesRecordsVideo/video/list.html.twig', [
            'videos' => $result['videos'],
            'total_videos' => $result['total_videos'],
            'has_more' => $result['has_more'],
            'api_url' => $this->generateUrl('vgr_video_list'),
        ]);
    }
}
