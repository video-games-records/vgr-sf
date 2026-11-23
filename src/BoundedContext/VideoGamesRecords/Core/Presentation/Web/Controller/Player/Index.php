<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player;

use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Player\PlayerListFilter;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\CountryRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\Core\Presentation\Form\PlayerFilterType;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\Repository\PlatformConnectionRepositoryInterface;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class Index extends AbstractLocalizedController
{
    private const int LIMIT = 100;

    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly CountryRepository $countryRepository,
        private readonly PlatformConnectionRepositoryInterface $platformConnectionRepository,
    ) {
    }

    #[Route('/players', name: 'vgr_player_index')]
    public function index(Request $request, string $_locale): Response
    {
        $sortBy = $request->query->get('sort', 'pointGame');
        $order = $request->query->get('order', 'DESC');
        $page = max(1, (int) $request->query->get('page', 1));

        $form = $this->createForm(PlayerFilterType::class, ['sort' => $sortBy, 'order' => $order], [
            'countries' => $this->countryRepository->findAllOrderedByName(),
            'locale' => $_locale,
        ]);
        $form->handleRequest($request);

        $filterData = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $filterData = $form->getData();
        }

        $filter = $this->resolveFilter($filterData);

        $limit = self::LIMIT;
        $offset = ($page - 1) * $limit;

        $players = $this->playerRepository->findForList($filter, $sortBy, $order, $limit, $offset);
        $totalPlayers = $this->playerRepository->countForList($filter);
        $totalPages = max(1, (int) ceil($totalPlayers / $limit));

        $playerIds = array_values(array_filter(
            array_map(static fn($player): ?int => $player->getId(), $players),
            static fn(?int $id): bool => $id !== null,
        ));
        $connectionsByPlayer = $this->platformConnectionRepository->findByPlayerIds($playerIds);

        return $this->render('@VideoGamesRecordsCore/player/index.html.twig', [
            'players' => $players,
            'connectionsByPlayer' => $connectionsByPlayer,
            'currentSort' => $sortBy,
            'currentOrder' => $order,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalPlayers' => $totalPlayers,
            'limit' => $limit,
            'filterForm' => $form,
            'activeFilterCount' => $this->countActiveFilters($request),
            'hasActiveFilters' => !$filter->isEmpty(),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function resolveFilter(array $data): PlayerListFilter
    {
        $platforms = array_filter(array_map(
            static fn(string $value): ?PlatformEnum => PlatformEnum::tryFrom($value),
            $data['platforms'] ?? [],
        ));

        $lastActive = $data['lastActive'] ?? null;

        $country = $data['country'] ?? null;

        return new PlayerListFilter(
            search: ($data['q'] ?? null) !== '' ? ($data['q'] ?? null) : null,
            countryId: $country !== null && $country !== '' ? (int) $country : null,
            teamId: !empty($data['team']) ? (int) $data['team'] : null,
            platforms: array_values($platforms),
            pointsMin: $data['pointsMin'] ?? null,
            pointsMax: $data['pointsMax'] ?? null,
            lastActiveSince: $this->resolveLastActiveSince($lastActive),
            neverActive: $lastActive === 'never',
            hasBadges: (bool) ($data['hasBadges'] ?? false),
        );
    }

    private function resolveLastActiveSince(?string $choice): ?\DateTimeImmutable
    {
        return match ($choice) {
            'today' => new \DateTimeImmutable('-1 day'),
            'week' => new \DateTimeImmutable('-7 days'),
            'month' => new \DateTimeImmutable('-30 days'),
            'year' => new \DateTimeImmutable('-1 year'),
            default => null,
        };
    }

    private function countActiveFilters(Request $request): int
    {
        $count = 0;
        foreach (['q', 'country', 'team', 'pointsMin', 'pointsMax', 'lastActive'] as $field) {
            if ($request->query->get($field)) {
                ++$count;
            }
        }

        $platforms = $request->query->all('platforms');
        if ($platforms !== []) {
            ++$count;
        }

        if ($request->query->getBoolean('hasBadges')) {
            ++$count;
        }

        return $count;
    }
}
