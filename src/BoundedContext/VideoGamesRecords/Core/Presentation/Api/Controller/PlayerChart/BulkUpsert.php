<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Api\Controller\PlayerChart;

use App\BoundedContext\VideoGamesRecords\Core\Application\Service\PlayerScoreFormService;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BulkUpsert extends AbstractController
{
    public function __construct(
        private readonly UserProvider $userProvider,
        private readonly PlayerScoreFormService $playerScoreFormService,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $player = $this->userProvider->getPlayer();

        if ($player === null) {
            return new JsonResponse(['error' => 'Player not found'], Response::HTTP_FORBIDDEN);
        }

        $content = json_decode($request->getContent(), true);

        if (!isset($content['playerCharts']) || !is_array($content['playerCharts'])) {
            return new JsonResponse(['error' => 'playerCharts array is required'], Response::HTTP_BAD_REQUEST);
        }

        $formData = $this->transformToFormData($content['playerCharts']);

        if (empty($formData)) {
            return new JsonResponse(['error' => 'No valid data provided'], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->playerScoreFormService->processSubmission($player, $formData);

        return new JsonResponse([
            'success' => true,
            'created' => $result['created'],
            'updated' => $result['updated'],
            'total' => $result['created'] + $result['updated'],
        ]);
    }

    /**
     * Transform ChartFormDataDTO-format input to PlayerScoreFormService format.
     *
     * Input (mirrors ChartFormDataDTO structure from form-data endpoints):
     * [
     *   {
     *     "id": 123,                          // chart id
     *     "playerChart": {                    // PlayerChartFormDTO
     *       "platform": {"id": 5} | null,     // object or null
     *       "libs": [                         // PlayerChartLibFormDTO[]
     *         {"libChartId": 456, "parseValue": [{"value": "100"}, {"value": "50"}]}
     *       ]
     *     }
     *   }
     * ]
     *
     * @param array<int, array<string, mixed>> $playerCharts
     * @return array<int, array<string, mixed>>
     */
    private function transformToFormData(array $playerCharts): array
    {
        $formData = [];

        foreach ($playerCharts as $item) {
            $chartId = $item['id'] ?? null;
            $playerChart = $item['playerChart'] ?? null;

            if ($chartId === null || $playerChart === null || !is_array($playerChart)) {
                continue;
            }

            // platform: null, {"id": 5, ...} or just an int
            $platformId = null;
            $platform = $playerChart['platform'] ?? null;
            if (is_array($platform) && isset($platform['id'])) {
                $platformId = $platform['id'];
            } elseif (is_numeric($platform)) {
                $platformId = $platform;
            }

            $libs = [];
            foreach ($playerChart['libs'] ?? [] as $lib) {
                $libChartId = $lib['libChartId'] ?? null;
                $parseValue = $lib['parseValue'] ?? [];

                if ($libChartId === null) {
                    continue;
                }

                $values = [];
                foreach ($parseValue as $index => $part) {
                    $values[$index] = $part['value'] ?? '';
                }

                $libs[$libChartId] = ['values' => $values];
            }

            $formData[$chartId] = [
                'modified' => '1',
                'platform' => $platformId,
                'libs' => $libs,
            ];
        }

        return $formData;
    }
}
