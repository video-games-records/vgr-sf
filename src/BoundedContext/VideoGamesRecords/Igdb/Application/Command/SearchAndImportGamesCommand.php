<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Igdb\Application\Command;

use App\BoundedContext\VideoGamesRecords\Igdb\Application\Service\IgdbImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'igdb:search-import:games',
    description: 'Search and import specific games from IGDB API by name and platform'
)]
class SearchAndImportGamesCommand extends Command
{
    public function __construct(
        private IgdbImportService $igdbImportService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Game name to search for'
            )
            ->addOption(
                'platform',
                'p',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Platform ID(s) to filter by'
            )
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Maximum number of results to process',
                10
            )
            ->addOption(
                'exact-match',
                'x',
                InputOption::VALUE_NONE,
                'Only import games with exact name match'
            )
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'Show what would be imported without actually importing'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $gameName = $input->getArgument('name');
        $platformIdsOption = $input->getOption('platform');
        $limit = (int) $input->getOption('limit');
        $exactMatch = $input->getOption('exact-match');
        $dryRun = $input->getOption('dry-run');

        // Convert platform IDs to integers
        /** @var array<int>|null $platformIds */
        $platformIds = null;
        if (is_array($platformIdsOption) && !empty($platformIdsOption)) {
            $platformIds = array_map('intval', $platformIdsOption);
        }

        $io->title("Searching for games matching: '$gameName'");

        try {
            $result = $this->igdbImportService->searchAndImportGames(
                $gameName,
                $platformIds,
                $limit,
                $exactMatch,
                $dryRun
            );

            if ($result['found'] === 0) {
                $io->warning('No games found matching your criteria.');
                return Command::SUCCESS;
            }

            if ($dryRun) {
                $io->note('Dry run mode - no games were imported.');
                $io->info("Found {$result['found']} games, {$result['toImport']} would be imported.");
            } else {
                $io->success([
                    "Search and import completed!",
                    "Found: {$result['found']} games",
                    "Imported: {$result['imported']} games",
                    "Skipped: {$result['skipped']} games (already exist)"
                ]);
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to search and import games: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
