<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Serie;

use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\SerieStatus;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\SerieRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class Index extends AbstractLocalizedController
{
    public function __construct(
        private readonly SerieRepository $serieRepository
    ) {
    }

    #[Route('/series', name: 'vgr_serie_index')]
    public function index(): Response
    {
        $series = $this->serieRepository->findBy(['status' => SerieStatus::ACTIVE], ['libSerie' => 'ASC']);

        return $this->render('@VideoGamesRecordsCore/serie/index.html.twig', [
            'series' => $series,
        ]);
    }
}
