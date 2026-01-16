<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// TODO: Migrate GameMatchingService to Core context
// use App\BoundedContext\VideoGamesRecords\Core\Domain\Service\GameMatchingService;

#[AsCommand(
    name: 'vgr:core:igdb:match-games',
    description: 'Automatically match Core Bundle games with IGDB games'
)]
class IgdbGameMatchingCommand extends Command
{
    // TODO: Inject dependencies once GameMatchingService is migrated
    // public function __construct(
    //     private readonly EntityManagerInterface $entityManager,
    //     private readonly GameMatchingService $gameMatchingService
    // ) {
    //     parent::__construct();
    // }

    protected function configure(): void
    {
        $this
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'Show what would be matched without actually updating the database'
            )
            ->addOption(
                'game-id',
                'g',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Process only specific game IDs (can be used multiple times)'
            )
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Maximum number of games to process',
                100
            )
            ->addOption(
                'batch-size',
                'b',
                InputOption::VALUE_OPTIONAL,
                'Number of games to process in each batch before flushing',
                10
            )
            ->addOption(
                'without-igdb-only',
                'w',
                InputOption::VALUE_NONE,
                'Process only games that do not have an IGDB association yet'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('IGDB Game Matching Service');

        // TODO: Remove this check once GameMatchingService is migrated
        $io->error('GameMatchingService needs to be migrated to Core context before this command can be used.');
        return Command::FAILURE;
    }
}
