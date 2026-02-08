<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Service;

use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerChartRank;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartLib;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChartLib;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\ChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlatformRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class PlayerScoreFormService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ChartRepository $chartRepository,
        private PlatformRepository $platformRepository,
        private MessageBusInterface $messageBus,
    ) {
    }

    /**
     * Process form submission and create/update player scores
     *
     * @param Player $player
     * @param array<int, array{libs?: array<int, array{values?: array<int, string>}>, platform?: string, modified?: string}> $formData
     * @return array{created: int, updated: int, chartIds: array<int>}
     * @throws ExceptionInterface
     */
    public function processSubmission(Player $player, array $formData): array
    {
        $created = 0;
        $updated = 0;
        $chartIds = [];

        foreach ($formData as $chartId => $chartData) {
            // Skip if not marked as modified by JavaScript
            if (!isset($chartData['modified'])) {
                continue;
            }

            // Skip if no libs data provided
            if (!isset($chartData['libs']) || empty($chartData['libs'])) {
                continue;
            }

            // Check if any value is actually provided
            if (!$this->hasAnyValue($chartData['libs'])) {
                continue;
            }

            $chart = $this->chartRepository->find($chartId);
            if (!$chart) {
                continue;
            }

            // Find existing PlayerChart or create new one
            $playerChart = $this->findOrCreatePlayerChart($player, $chart);
            $isNew = $playerChart->getId() === null;

            // Process libs
            $this->processChartLibs($playerChart, $chart, $chartData['libs']);

            // Process platform
            $platformId = $chartData['platform'] ?? null;
            if ($platformId !== null && $platformId !== '') {
                $platform = $this->platformRepository->find((int) $platformId);
                $playerChart->setPlatform($platform);
            } else {
                $playerChart->setPlatform(null);
            }

            // Reset status to NONE when score is posted/updated
            $playerChart->setStatus(PlayerChartStatusEnum::NONE);

            // Update lastUpdate
            $playerChart->setLastUpdate(new \DateTime());

            $this->entityManager->persist($playerChart);

            if ($isNew) {
                $created++;
            } else {
                $updated++;
            }

            $chartIds[] = $chartId;
        }

        if (!empty($chartIds)) {
            $this->entityManager->flush();

            // Dispatch ranking update messages
            foreach ($chartIds as $chartId) {
                $this->messageBus->dispatch(new UpdatePlayerChartRank($chartId));
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'chartIds' => $chartIds,
        ];
    }

    /**
     * Check if any value is provided in the libs data
     *
     * @param array<int|string, array<string, mixed>> $libsData
     */
    private function hasAnyValue(array $libsData): bool
    {
        foreach ($libsData as $libData) {
            if (isset($libData['values']) && is_array($libData['values'])) {
                foreach ($libData['values'] as $value) {
                    if ((string) $value !== '') {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function findOrCreatePlayerChart(Player $player, Chart $chart): PlayerChart
    {
        // Try to find existing PlayerChart
        foreach ($chart->getPlayerCharts() as $playerChart) {
            if ($playerChart->getPlayer()->getId() === $player->getId()) {
                return $playerChart;
            }
        }

        // Create new PlayerChart
        $playerChart = new PlayerChart();
        $playerChart->setPlayer($player);
        $playerChart->setChart($chart);
        $playerChart->setStatus(PlayerChartStatusEnum::NONE);

        return $playerChart;
    }

    /**
     * Process chart libs from form data
     *
     * @param PlayerChart $playerChart
     * @param Chart $chart
     * @param array<int|string, array<string, mixed>> $libsData
     */
    private function processChartLibs(PlayerChart $playerChart, Chart $chart, array $libsData): void
    {
        // Get existing PlayerChartLibs indexed by ChartLib ID
        $existingLibs = [];
        foreach ($playerChart->getLibs() as $playerChartLib) {
            $libChartId = $playerChartLib->getLibChart()->getId();
            if ($libChartId !== null) {
                $existingLibs[$libChartId] = $playerChartLib;
            }
        }

        /** @var ChartLib $chartLib */
        foreach ($chart->getLibs() as $chartLib) {
            $libId = $chartLib->getId();
            if ($libId === null) {
                continue;
            }

            if (!isset($libsData[$libId]) || !isset($libsData[$libId]['values']) || !is_array($libsData[$libId]['values'])) {
                continue;
            }

            $values = $libsData[$libId]['values'];

            // Convert indexed array to parseValue format (matches ScoreTools::getValues output)
            /** @var array<int, array{value: string}> $parseValue */
            $parseValue = [];
            foreach ($values as $index => $value) {
                $parseValue[(int) $index] = ['value' => (string) $value];
            }

            // Find or create PlayerChartLib
            if (isset($existingLibs[$libId])) {
                $playerChartLib = $existingLibs[$libId];
            } else {
                $playerChartLib = new PlayerChartLib();
                $playerChartLib->setLibChart($chartLib);
                $playerChart->addLib($playerChartLib);
            }

            // Set parseValue and convert to BDD value
            // @phpstan-ignore-next-line Entity PHPDoc declares array<string, mixed> but uses numeric indices
            $playerChartLib->setParseValue($parseValue);
            $playerChartLib->setValueFromPaseValue();
        }
    }
}
