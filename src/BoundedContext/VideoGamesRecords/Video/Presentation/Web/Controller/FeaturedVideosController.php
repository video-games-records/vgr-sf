<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Video\Presentation\Web\Controller;

use App\BoundedContext\VideoGamesRecords\Video\Infrastructure\Doctrine\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FeaturedVideosController extends AbstractController
{
    public function __construct(
        private readonly VideoRepository $videoRepository
    ) {
    }

    public function latest(): Response
    {
        $videos = $this->videoRepository->findLatestActive(4);

        return $this->render('@VideoGamesRecordsVideo/featured/latest_videos.html.twig', [
            'videos' => $videos,
        ]);
    }

    public function popular(): Response
    {
        $videos = $this->videoRepository->findMostPopularActive(4);

        return $this->render('@VideoGamesRecordsVideo/featured/popular_videos.html.twig', [
            'videos' => $videos,
        ]);
    }
}
