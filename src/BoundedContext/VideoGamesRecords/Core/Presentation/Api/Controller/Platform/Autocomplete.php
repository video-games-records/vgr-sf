<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Api\Controller\Platform;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlatformRepository;

class Autocomplete extends AbstractController
{
    public function __construct(
        private readonly PlatformRepository $platformRepository,
    ) {
    }

    #[Route('/api/platforms/autocomplete', name: 'vgr_api_platform_autocomplete', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $q = $request->query->get('query', '');

        $platforms = $this->platformRepository->autocomplete($q);

        $results = array_map(fn($platform) => [
            'id' => $platform->getId(),
            'text' => $platform->getName(),
        ], $platforms);

        return $this->json($results);
    }
}
