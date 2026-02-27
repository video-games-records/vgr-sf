<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Application\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[AsCommand(
    name: 'forum:fix-slugs',
    description: 'Fix invalid slugs in Forum topics and forums (accented characters)'
)]
class FixSlugsCommand extends Command
{
    public function __construct(
        private Connection $connection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be changed without applying');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');

        if ($dryRun) {
            $io->note('Mode dry-run activé, aucune modification ne sera appliquée.');
        }

        $slugger = new AsciiSlugger();

        $topicCount = $this->fixTable($io, $slugger, 'pnf_topic', 'name', $dryRun);
        $forumCount = $this->fixTable($io, $slugger, 'pnf_forum', 'lib_forum', $dryRun);

        $total = $topicCount + $forumCount;

        if ($total === 0) {
            $io->success('Aucun slug invalide trouvé.');
        } elseif ($dryRun) {
            $io->warning(sprintf('%d slug(s) à corriger. Relancez sans --dry-run pour appliquer.', $total));
        } else {
            $io->success(sprintf('%d slug(s) corrigé(s).', $total));
        }

        return Command::SUCCESS;
    }

    private function fixTable(SymfonyStyle $io, AsciiSlugger $slugger, string $table, string $nameColumn, bool $dryRun): int
    {
        $io->section(sprintf('Table: %s', $table));

        $rows = $this->connection->fetchAllAssociative(
            sprintf("SELECT id, %s, slug FROM %s WHERE slug REGEXP '[^a-z0-9\\-]'", $nameColumn, $table)
        );

        if (count($rows) === 0) {
            $io->info('Aucun slug invalide.');
            return 0;
        }

        $io->info(sprintf('%d slug(s) invalide(s) trouvé(s).', count($rows)));

        $existingSlugs = array_column(
            $this->connection->fetchAllAssociative(sprintf('SELECT slug FROM %s', $table)),
            'slug'
        );

        $fixed = 0;

        foreach ($rows as $row) {
            $newSlug = strtolower((string) $slugger->slug($row[$nameColumn]));
            $newSlug = $this->makeUnique($newSlug, $row['id'], $existingSlugs);

            $io->text(sprintf(
                '  [ID %d] "%s" → "%s"',
                $row['id'],
                $row['slug'],
                $newSlug,
            ));

            if (!$dryRun) {
                $this->connection->update($table, ['slug' => $newSlug], ['id' => $row['id']]);
            }

            $existingSlugs[] = $newSlug;
            $fixed++;
        }

        return $fixed;
    }

    /**
     * @param list<string> $existingSlugs
     */
    private function makeUnique(string $slug, int $currentId, array $existingSlugs): string
    {
        if (!in_array($slug, $existingSlugs, true)) {
            return $slug;
        }

        $i = 1;
        while (in_array($slug . '-' . $i, $existingSlugs, true)) {
            $i++;
        }

        return $slug . '-' . $i;
    }
}
