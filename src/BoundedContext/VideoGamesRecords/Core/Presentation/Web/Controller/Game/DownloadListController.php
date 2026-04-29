<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Game;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameDownloadUrlRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class DownloadListController extends AbstractLocalizedController
{
    private const int GAMES_PER_PAGE = 12;

    public function __construct(
        private readonly GameDownloadUrlRepository $gameDownloadUrlRepository
    ) {
    }

    #[Route('/downloads', name: 'vgr_core_game_download_list')]
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));

        // Query builder pour les jeux avec téléchargements
        $queryBuilder = $this->gameDownloadUrlRepository
            ->createQueryBuilder('gdu')
            ->select('gdu, g, p')
            ->leftJoin('gdu.game', 'g')
            ->leftJoin('gdu.platform', 'p')
            ->orderBy('g.libGameEn', 'ASC')
            ->addOrderBy('p.name', 'ASC');

        $paginator = new Paginator($queryBuilder);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * self::GAMES_PER_PAGE)
            ->setMaxResults(self::GAMES_PER_PAGE);

        $totalResults = count($paginator);

        // Grouper les téléchargements par jeu
        $gamesWithDownloads = [];
        foreach ($paginator as $downloadUrl) {
            $game = $downloadUrl->getGame();
            if (!isset($gamesWithDownloads[$game->getId()])) {
                $gamesWithDownloads[$game->getId()] = [
                    'game' => $game,
                    'downloads' => []
                ];
            }
            $gamesWithDownloads[$game->getId()]['downloads'][] = $downloadUrl;
        }

        // Calculer les infos de pagination
        $totalPages = ceil($totalResults / self::GAMES_PER_PAGE);
        $pagination = [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_results' => $totalResults,
            'per_page' => self::GAMES_PER_PAGE,
            'has_previous' => $page > 1,
            'has_next' => $page < $totalPages,
            'previous_page' => $page > 1 ? $page - 1 : null,
            'next_page' => $page < $totalPages ? $page + 1 : null,
        ];

        return $this->render('@VideoGamesRecordsCore/game/download_list.html.twig', [
            'gamesWithDownloads' => array_values($gamesWithDownloads),
            'pagination' => $pagination,
        ]);
    }
}
