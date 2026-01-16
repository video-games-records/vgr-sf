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
    name: 'igdb:import:platform-types',
    description: 'Import platform types from IGDB API into database'
)]
class ImportPlatformTypesCommand extends Command
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
                'Limit the number of platform types to import',
                50
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = (int) $input->getOption('limit');

        $io->title('Importing platform types from IGDB API');

        try {
            $result = $this->igdbImportService->importPlatformTypes($limit);

            $io->success([
                "Platform types imported successfully!",
                "Inserted: {$result['inserted']} platform types",
                "Updated: {$result['updated']} platform types",
                "Skipped: {$result['skipped']} platform types (no changes)",
                "Total processed: {$result['total']} platform types"
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to import platform types: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
