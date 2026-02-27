<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Application\Command;

use App\BoundedContext\Forum\Application\Service\BbCodeToHtmlService;
use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'forum:messages:convert-bbcode',
    description: 'Convert BBCode to HTML in all forum messages'
)]
class ConvertBbCodeToHtmlCommand extends Command
{
    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly BbCodeToHtmlService $bbCodeToHtmlService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Simulate the conversion without saving changes'
            )
            ->addOption(
                'batch-size',
                null,
                InputOption::VALUE_REQUIRED,
                'Number of messages to flush per batch',
                '100'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $isDryRun = (bool) $input->getOption('dry-run');
        $batchSize = (int) $input->getOption('batch-size');

        $io->title('Forum - Convert BBCode to HTML');

        if ($isDryRun) {
            $io->note('Dry-run mode enabled: no changes will be saved.');
        }

        $messages = $this->messageRepository->findAll();
        $total = count($messages);

        $io->writeln(sprintf('Total messages found: <info>%d</info>', $total));

        $converted = 0;
        $skipped = 0;
        $processed = 0;

        $io->progressStart($total);

        foreach ($messages as $message) {
            $original = $message->getMessage();

            if (!$this->bbCodeToHtmlService->hasBbCode($original)) {
                $skipped++;
                $io->progressAdvance();
                continue;
            }

            $html = $this->bbCodeToHtmlService->convert($original);
            $message->setMessage($html);
            $converted++;
            $processed++;

            if (!$isDryRun && $processed % $batchSize === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }

            $io->progressAdvance();
        }

        if (!$isDryRun && $processed % $batchSize !== 0) {
            $this->entityManager->flush();
        }

        $io->progressFinish();

        $io->success([
            sprintf('Conversion complete!'),
            sprintf('Converted : %d messages', $converted),
            sprintf('Skipped   : %d messages (no BBCode detected)', $skipped),
            sprintf('Total     : %d messages processed', $total),
        ]);

        return Command::SUCCESS;
    }
}
