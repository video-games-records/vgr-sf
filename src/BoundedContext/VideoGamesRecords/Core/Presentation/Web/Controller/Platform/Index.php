<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Platform;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlatformRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class Index extends AbstractLocalizedController
{
    public function __construct(
        private readonly PlatformRepository $platformRepository
    ) {
    }

    #[Route('/platforms', name: 'vgr_platform_index')]
    public function index(): Response
    {
        $platforms = $this->platformRepository->findBy(['status' => 'ACTIF'], ['name' => 'ASC']);

        return $this->render('@VideoGamesRecordsCore/platform/index.html.twig', [
            'platforms' => $platforms,
        ]);
    }
}
