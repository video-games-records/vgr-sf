<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Igdb\Application\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\BoundedContext\VideoGamesRecords\Igdb\Application\Service\IgdbImportService;

#[AsCommand(
    name: 'igdb:import:games',
    description: 'Import all games from IGDB API into database'
)]
class ImportGamesCommand extends Command
{
    public function __construct(
        private IgdbImportService $igdbImportService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Importing games from IGDB API');

        try {
            $result = $this->igdbImportService->importGames();

            $io->success([
                "Games imported successfully!",
                "Inserted: {$result['inserted']} games",
                "Updated: {$result['updated']} games",
                "Skipped: {$result['skipped']} games (no changes)",
                "Total processed: " . ($result['inserted'] + $result['updated'] + $result['skipped']) . " games"
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to import games: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
