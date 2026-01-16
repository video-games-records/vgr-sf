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
    name: 'igdb:import:platform-logos',
    description: 'Import platform logos from IGDB API into database'
)]
class ImportPlatformLogosCommand extends Command
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
                'Limit the number of platform logos to import',
                100
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = (int) $input->getOption('limit');

        $io->title('Importing platform logos from IGDB API');

        try {
            $result = $this->igdbImportService->importPlatformLogos($limit);

            $io->success([
                "Platform logos imported successfully!",
                "Inserted: {$result['inserted']} platform logos",
                "Updated: {$result['updated']} platform logos",
                "Skipped: {$result['skipped']} platform logos (no changes)",
                "Total processed: {$result['total']} platform logos"
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to import platform logos: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
