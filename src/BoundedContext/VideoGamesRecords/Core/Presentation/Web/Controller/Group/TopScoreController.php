<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Group;

use App\BoundedContext\VideoGamesRecords\Core\Application\DataProvider\TopScoreProvider;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Exception\ORMException;

class TopScoreController extends AbstractController
{
    public function __construct(
        private readonly TopScoreProvider $topScoreProvider,
        private readonly GroupRepository $groupRepository
    ) {
    }

    /**
     * @throws ORMException
     */
    public function index(int $groupId, Request $request): Response
    {
        $group = $this->groupRepository->find($groupId);

        if (!$group) {
            throw $this->createNotFoundException('Group not found');
        }

        $locale = $request->getLocale();
        $charts = $this->topScoreProvider->load($group, $locale);

        return $this->render('@VideoGamesRecordsCore/group/_top_scores.html.twig', [
            'group' => $group,
            'charts' => $charts
        ]);
    }
}
