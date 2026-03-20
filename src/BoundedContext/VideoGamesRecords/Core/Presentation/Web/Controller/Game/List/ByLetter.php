<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Game\List;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class ByLetter extends AbstractLocalizedController
{
    public function __construct(
        private readonly GameRepository $gameRepository
    ) {
    }

    #[Route('/games/{letter}', name: 'vgr_game_list_by_letter', requirements: ['letter' => '[a-z0]'])]
    public function list(string $letter, string $_locale): Response
    {
        $games = $this->gameRepository->findWithLetter($letter, $_locale)->getResult();

        // Generate letters array for navigation
        $letters = array_merge(['0'], range('a', 'z'));

        return $this->render('@VideoGamesRecordsCore/game/list_by_letter.html.twig', [
            'games' => $games,
            'currentLetter' => $letter,
            'letters' => $letters,
        ]);
    }
}
