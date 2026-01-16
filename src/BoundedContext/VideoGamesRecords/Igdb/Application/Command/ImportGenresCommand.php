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
    name: 'igdb:import:genres',
    description: 'Import all genres from IGDB API into database'
)]
class ImportGenresCommand extends Command
{
    public function __construct(
        private IgdbImportService $igdbImportService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Importing genres from IGDB API');

        try {
            $result = $this->igdbImportService->importGenres();

            $io->success([
                "Genres imported successfully!",
                "Inserted: {$result['inserted']} genres",
                "Updated: {$result['updated']} genres",
                "Skipped: {$result['skipped']} genres (no changes)",
                "Total processed: " . ($result['inserted'] + $result['updated'] + $result['skipped']) . " genres"
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to import genres: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
