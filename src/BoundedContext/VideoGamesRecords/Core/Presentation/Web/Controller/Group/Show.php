<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Group;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GroupRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class Show extends AbstractLocalizedController
{
    public function __construct(
        private readonly GroupRepository $groupRepository
    ) {
    }

    #[Route('/game/{id}-{slug}/group/{groupId}-{groupSlug}', name: 'vgr_group_show', requirements: ['id' => '\d+', 'groupId' => '\d+'])]
    public function show(int $id, string $slug, int $groupId, string $groupSlug): Response
    {
        $group = $this->groupRepository->find($groupId);

        if (!$group || $group->getSlug() !== $groupSlug) {
            throw $this->createNotFoundException('Group not found');
        }

        // Verify that the group belongs to the specified game
        if ($group->getGame()->getId() !== $id || $group->getGame()->getSlug() !== $slug) {
            throw $this->createNotFoundException('Group does not belong to this game');
        }

        $game = $group->getGame();

        return $this->render('@VideoGamesRecordsCore/group/show.html.twig', [
            'game' => $game,
            'group' => $group,
        ]);
    }
}
