<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Country;

use App\BoundedContext\VideoGamesRecords\Core\Application\DataProvider\Ranking\PlayerCountryRankingProvider;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\CountryRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class Show extends AbstractLocalizedController
{
    public function __construct(
        private readonly CountryRepository $countryRepository,
        private readonly PlayerCountryRankingProvider $rankingProvider,
    ) {
    }

    #[Route('/country/{id}-{slug}', name: 'vgr_country_show', requirements: ['id' => '\d+'])]
    public function __invoke(int $id, string $slug): Response
    {
        $country = $this->countryRepository->find($id);

        if (!$country || $country->getSlug() !== $slug) {
            throw $this->createNotFoundException('Country not found');
        }

        $countries = $this->countryRepository->findAllOrderedByName();

        $rankingPoints = $this->rankingProvider->getRankingPoints($id, ['maxRank' => 100]);

        return $this->render('@VideoGamesRecordsCore/country/show.html.twig', [
            'country' => $country,
            'countries' => $countries,
            'rankingPoints' => $rankingPoints,
        ]);
    }
}
