<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Command;

use App\BoundedContext\Forum\Application\Service\BbCodeToHtmlService;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

#[AsCommand(
    name: 'vgr:player:migrate-bbcode',
    description: 'Convert BBCode to HTML in player presentation and collection fields'
)]
class MigratePlayerBbCodeCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly BbCodeToHtmlService $bbCodeToHtmlService,
        #[Autowire(service: 'html_sanitizer.sanitizer.app.content_sanitizer')]
        private readonly HtmlSanitizerInterface $sanitizer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Preview changes without saving');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');

        if ($dryRun) {
            $io->note('Dry-run mode — no changes will be saved.');
        }

        $conn = $this->em->getConnection();

        $ids = $conn->fetchFirstColumn(
            "SELECT id FROM vgr_player
             WHERE presentation REGEXP :pattern OR collection REGEXP :pattern",
            ['pattern' => '\\[/?(?:b|i|u|s|url|img|quote|code|list|color|size|center)[\\]=\\s]']
        );

        if (empty($ids)) {
            $io->success('No BBCode found in player fields.');
            return Command::SUCCESS;
        }

        $io->info(sprintf('%d player(s) to migrate.', count($ids)));

        $updated = 0;

        foreach ($ids as $id) {
            /** @var Player $player */
            $player = $this->em->find(Player::class, (int) $id);

            if (!$player) {
                continue;
            }

            $changed = false;

            if ($player->getPresentation() && $this->bbCodeToHtmlService->hasBbCode($player->getPresentation())) {
                $html = $this->convert($player->getPresentation());
                if ($dryRun) {
                    $io->section(sprintf('Player #%d — presentation', $id));
                    $io->text('<fg=red>Before:</> ' . OutputFormatter::escape(substr($player->getPresentation(), 0, 300)));
                    $io->text('<fg=green>After:</>  ' . OutputFormatter::escape(substr($html, 0, 300)));
                } else if ($html !== $player->getPresentation()) {
                    $player->setPresentation($html);
                    $changed = true;
                }
            }

            if ($player->getCollection() && $this->bbCodeToHtmlService->hasBbCode($player->getCollection())) {
                $html = $this->convert($player->getCollection());
                if ($dryRun) {
                    $io->section(sprintf('Player #%d — collection', $id));
                    $io->text('<fg=red>Before:</> ' . OutputFormatter::escape(substr($player->getCollection(), 0, 300)));
                    $io->text('<fg=green>After:</>  ' . OutputFormatter::escape(substr($html, 0, 300)));
                } else if ($html !== $player->getCollection()) {
                    $player->setCollection($html);
                    $changed = true;
                }
            }

            if ($changed) {
                ++$updated;
                $this->em->flush();
            }

            $this->em->detach($player);
        }

        if (!$dryRun) {
            $io->success(sprintf('%d player(s) migrated.', $updated));
        }

        return Command::SUCCESS;
    }

    private function convert(string $text): string
    {
        $html = $this->bbCodeToHtmlService->convert($text);
        $html = $this->unwrapUnsupportedTags($html);
        return $this->sanitizer->sanitize($html);
    }

    /**
     * The sanitizer drops non-allowlisted elements AND their children entirely.
     * Strip these wrappers first so their text content survives sanitization.
     */
    private function unwrapUnsupportedTags(string $html): string
    {
        // <div ...>content</div> → content  ([center] output)
        $html = preg_replace('/<div[^>]*>(.*?)<\/div>/is', '$1', $html) ?? $html;
        // <span ...>content</span> → content  ([color] output)
        $html = preg_replace('/<span[^>]*>(.*?)<\/span>/is', '$1', $html) ?? $html;
        // <s>content</s> → content  ([s] output)
        $html = preg_replace('/<s>(.*?)<\/s>/is', '$1', $html) ?? $html;
        // <pre><code>content</code></pre> → content  ([code] output)
        $html = preg_replace('/<pre><code>(.*?)<\/code><\/pre>/is', '$1', $html) ?? $html;

        return $html;
    }
}
