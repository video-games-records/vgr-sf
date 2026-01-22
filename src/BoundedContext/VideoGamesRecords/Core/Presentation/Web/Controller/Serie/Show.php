<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Serie;

use App\BoundedContext\VideoGamesRecords\Core\Application\DataProvider\Ranking\PlayerSerieRankingProvider;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\SerieRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Show extends AbstractLocalizedController
{
    public function __construct(
        private readonly SerieRepository $serieRepository,
        private readonly PlayerSerieRankingProvider $rankingProvider
    ) {
    }

    /**
     * @throws ORMException
     */
    #[Route('/serie/{id}-{slug}', name: 'vgr_serie_show', requirements: ['id' => '\d+'])]
    public function show(int $id, string $slug, string $tab = 'games'): Response
    {
        $serie = $this->serieRepository->find($id);

        if (!$serie || $serie->getSlug() !== $slug) {
            throw $this->createNotFoundException('Serie not found');
        }

        // Get games for this serie
        $games = $serie->getGames()->toArray();

        // Sort games by name
        usort($games, fn($a, $b) => strcmp($a->getName(), $b->getName()));

        // Get leaderboard rankings (Points)
        $rankingsPoints = $this->rankingProvider->getRankingPoints($id, ['maxRank' => 100]);

        // Get leaderboard rankings (Medals)
        $rankingsMedals = $this->rankingProvider->getRankingMedals($id, ['maxRank' => 100]);

        return $this->render('@VideoGamesRecordsCore/serie/show.html.twig', [
            'serie' => $serie,
            'games' => $games,
            'rankingsPoints' => $rankingsPoints,
            'rankingsMedals' => $rankingsMedals,
            'activeTab' => $tab,
        ]);
    }
}
