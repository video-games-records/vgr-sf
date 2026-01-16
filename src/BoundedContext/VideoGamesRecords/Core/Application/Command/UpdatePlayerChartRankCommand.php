<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Command;

use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerChartRank;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\ChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GroupRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use DateTime;

#[AsCommand(
    name: 'vgr:update-player-chart-rank',
    description: 'Dispatch UpdatePlayerChartRank messages for charts'
)]
class UpdatePlayerChartRankCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly ChartRepository $chartRepository,
        private readonly GroupRepository $groupRepository,
        private readonly GameRepository $gameRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'chart-id',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Specific chart ID to update'
            )
            ->addOption(
                'group-id',
                'g',
                InputOption::VALUE_OPTIONAL,
                'Group ID - dispatch for all charts in this group'
            )
            ->addOption(
                'game-id',
                'G',
                InputOption::VALUE_OPTIONAL,
                'Game ID - dispatch for all charts in this game'
            )
            ->addOption(
                'last-update',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Date (YYYYMMDD) - dispatch for charts with player updates after this date'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $chartId = $input->getOption('chart-id');
        $groupId = $input->getOption('group-id');
        $gameId = $input->getOption('game-id');
        $lastUpdate = $input->getOption('last-update');

        // Validation: exactly one option must be provided
        $providedOptions = array_filter([$chartId, $groupId, $gameId, $lastUpdate]);
        if (count($providedOptions) !== 1) {
            $io->error('You must provide exactly one of: --chart-id, --group-id, --game-id, or --last-update');
            return Command::FAILURE;
        }

        $chartIds = [];

        if ($chartId) {
            $chart = $this->chartRepository->find((int) $chartId);
            if (!$chart) {
                $io->error(sprintf('Chart with ID %d not found', $chartId));
                return Command::FAILURE;
            }
            $chartIds = [(int) $chartId];
            $io->info(sprintf('Found 1 chart (ID: %d)', $chartId));
        } elseif ($groupId) {
            $group = $this->groupRepository->find((int) $groupId);
            if (!$group) {
                $io->error(sprintf('Group with ID %d not found', $groupId));
                return Command::FAILURE;
            }
            $charts = $group->getCharts();
            $chartIds = $charts->map(fn($chart) => $chart->getId())->toArray();
            $io->info(sprintf('Found %d charts in group "%s" (ID: %d)', count($chartIds), $group->getDefaultName(), $groupId));
        } elseif ($gameId) {
            $game = $this->gameRepository->find((int) $gameId);
            if (!$game) {
                $io->error(sprintf('Game with ID %d not found', $gameId));
                return Command::FAILURE;
            }
            $chartIds = [];
            foreach ($game->getGroups() as $group) {
                foreach ($group->getCharts() as $chart) {
                    $chartIds[] = $chart->getId();
                }
            }
            $io->info(sprintf('Found %d charts in game "%s" (ID: %d)', count($chartIds), $game->getDefaultName(), $gameId));
        } elseif ($lastUpdate) {
            // Validate date format
            if (!preg_match('/^\d{8}$/', $lastUpdate)) {
                $io->error('Invalid date format. Please use YYYYMMDD (e.g., 20251201)');
                return Command::FAILURE;
            }

            $date = DateTime::createFromFormat('Ymd', $lastUpdate);
            if (!$date) {
                $io->error('Invalid date. Please use YYYYMMDD format (e.g., 20251201)');
                return Command::FAILURE;
            }

            // Get unique chart IDs from PlayerChart with lastUpdate >= date
            $chartIds = $this->chartRepository->findChartIdsByLastUpdate($date);
            $io->info(sprintf('Found %d unique charts with player updates after %s', count($chartIds), $date->format('Y-m-d')));
        }

        if (empty($chartIds)) {
            $io->warning('No charts found to process');
            return Command::SUCCESS;
        }

        $io->progressStart(count($chartIds));

        foreach ($chartIds as $id) {
            $message = new UpdatePlayerChartRank($id);
            $this->messageBus->dispatch($message);
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success(sprintf('Successfully dispatched %d UpdatePlayerChartRank messages', count($chartIds)));

        return Command::SUCCESS;
    }
}
