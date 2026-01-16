<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Igdb\Application\Command;

use App\BoundedContext\VideoGamesRecords\Igdb\Application\Service\IgdbImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'igdb:import:platforms',
    description: 'Import platforms from IGDB API into database'
)]
class ImportPlatformsCommand extends Command
{
    public function __construct(
        private IgdbImportService $igdbImportService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'limit',
                null,
                InputOption::VALUE_OPTIONAL,
                'Limit the number of platforms to import',
                100
            )
            ->addOption(
                'offset',
                null,
                InputOption::VALUE_OPTIONAL,
                'Offset for platform import',
                0
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = (int) $input->getOption('limit');
        $offset = (int) $input->getOption('offset');

        $io->title('Importing platforms from IGDB API');

        try {
            $result = $this->igdbImportService->importPlatforms($limit, $offset);

            $io->success([
                "Platforms imported successfully!",
                "Inserted: {$result['inserted']} platforms",
                "Updated: {$result['updated']} platforms",
                "Skipped: {$result['skipped']} platforms (no changes)",
                "Total processed: {$result['total']} platforms"
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to import platforms: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
