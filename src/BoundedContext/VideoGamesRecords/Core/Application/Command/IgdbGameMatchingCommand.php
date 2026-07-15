<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Command;

use App\BoundedContext\VideoGamesRecords\Core\Application\Service\GameMatchingService;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'vgr:core:igdb:match-games',
    description: 'Automatically match Core VGR games with IGDB games by name and platform'
)]
class IgdbGameMatchingCommand extends Command
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly GameMatchingService $gameMatchingService,
    ) {
        parent::__construct();
    }

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
                'without-igdb-only',
                'w',
                InputOption::VALUE_NONE,
                'Process only games that do not have an IGDB association yet'
            )
            ->addOption(
                'local-only',
                null,
                InputOption::VALUE_NONE,
                'Match only against the local igdb_game table, without querying the IGDB API'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dryRun = (bool) $input->getOption('dry-run');
        $withoutIgdbOnly = (bool) $input->getOption('without-igdb-only');
        $localOnly = (bool) $input->getOption('local-only');
        $limit = (int) $input->getOption('limit');

        /** @var array<string> $gameIdsOption */
        $gameIdsOption = $input->getOption('game-id');
        $gameIds = !empty($gameIdsOption) ? array_map('intval', $gameIdsOption) : null;

        $io->title('IGDB Game Matching');

        if ($dryRun) {
            $io->note('Dry-run mode — no changes will be written to the database.');
        }

        $games = $this->gameRepository->findForIgdbMatching($withoutIgdbOnly, $gameIds, $limit);

        if (empty($games)) {
            $io->warning('No games found matching the given criteria.');
            return Command::SUCCESS;
        }

        $io->writeln(sprintf('Processing <info>%d</info> game(s)…', count($games)));

        $matched = 0;
        $ambiguous = 0;
        $noResult = 0;
        $errors = 0;

        foreach ($games as $game) {
            try {
                $result = $this->gameMatchingService->match($game, $dryRun, $localOnly);
            } catch (\Throwable $e) {
                $errors++;
                $io->writeln(sprintf(
                    '  <fg=red>!</> [%d] %s — error: %s',
                    $game->getId(),
                    $game->getLibGameEn(),
                    $e->getMessage()
                ));
                continue;
            }

            $igdbGame = $result->igdbGame;
            if ($igdbGame !== null) {
                $matched++;
                $io->writeln(sprintf(
                    '  <fg=green>✓</> [%d] %s → IGDB #%d (%s)%s',
                    $game->getId(),
                    $game->getLibGameEn(),
                    $igdbGame->getId(),
                    $igdbGame->getName(),
                    $result->fromApi ? ' <fg=cyan>[API]</>' : ''
                ));
            } elseif ($result->isAmbiguous()) {
                $ambiguous++;
                $io->writeln(sprintf(
                    '  <fg=yellow>~</> [%d] %s — %d candidates, ambiguous',
                    $game->getId(),
                    $game->getLibGameEn(),
                    $result->candidatesCount
                ));
            } else {
                $noResult++;
                $io->writeln(sprintf(
                    '  <fg=gray>✗</> [%d] %s — no match',
                    $game->getId(),
                    $game->getLibGameEn()
                ));
            }
        }

        $io->newLine();
        $io->success(sprintf(
            'Done — matched: %d | ambiguous: %d | no result: %d | errors: %d',
            $matched,
            $ambiguous,
            $noResult,
            $errors
        ));

        return Command::SUCCESS;
    }
}
