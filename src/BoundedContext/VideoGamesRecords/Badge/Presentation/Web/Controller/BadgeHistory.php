<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Presentation\Web\Controller;

use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository\BadgeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class BadgeHistory extends AbstractController
{
    public function __construct(
        private readonly BadgeRepository $badgeRepository,
    ) {
    }

    #[Route('/badge/{id}/history', name: 'vgr_badge_history_ajax', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function __invoke(int $id): Response
    {
        $badge = $this->badgeRepository->find($id);

        if (!$badge) {
            throw $this->createNotFoundException('Badge not found');
        }

        return $this->render('@VideoGamesRecordsBadge/_history_modal_content.html.twig', [
            'badge' => $badge,
        ]);
    }
}
