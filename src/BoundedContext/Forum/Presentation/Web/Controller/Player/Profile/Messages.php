<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Presentation\Web\Controller\Player\Profile;

use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\MessageRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Player\Profile\AbstractProfileController;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class Messages extends AbstractProfileController
{
    private const int MESSAGES_PER_PAGE = 20;

    public function __construct(
        PlayerRepository $playerRepository,
        private readonly MessageRepository $messageRepository
    ) {
        parent::__construct($playerRepository);
    }

    #[Route('/player/{id}-{slug}/messages', name: 'vgr_player_profile_messages', requirements: ['id' => '\d+'])]
    public function __invoke(int $id, string $slug, Request $request): Response
    {
        $player = $this->getPlayer($id, $slug);

        $page = max(1, (int) $request->query->get('page', 1));
        $search = trim((string) $request->query->get('q', ''));

        $queryBuilder = $this->messageRepository->getMessagesByUserQueryBuilder($player->getUserId(), $search ?: null);

        $paginator = new Paginator($queryBuilder);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * self::MESSAGES_PER_PAGE)
            ->setMaxResults(self::MESSAGES_PER_PAGE);

        $totalMessages = count($paginator);
        $totalPages = (int) ceil($totalMessages / self::MESSAGES_PER_PAGE);

        return $this->render('@Forum/player/profile/messages.html.twig', [
            'player' => $player,
            'messages' => $paginator,
            'current_tab' => 'messages',
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalMessages' => $totalMessages,
            'search' => $search,
        ]);
    }
}
