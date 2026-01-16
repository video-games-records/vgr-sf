<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Application\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Finder\Finder;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'vgr:shared:dispatch-message',
    description: 'Dispatch any message from VideoGamesRecords bounded contexts'
)]
class DispatchMessageCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly KernelInterface $kernel
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'context',
                InputArgument::REQUIRED,
                'Bounded Context name (e.g., Core, Team, Badge, Proof, Igdb)'
            )
            ->addArgument(
                'message-name',
                InputArgument::REQUIRED,
                'Name of the message class (e.g., UpdatePlayerData, UpdateTeamRank)'
            )
            ->addArgument(
                'id',
                InputArgument::REQUIRED,
                'ID to pass to the message constructor'
            )
            ->setHelp('
This command allows you to dispatch any message from VideoGamesRecords bounded contexts.

Examples:
  <info>php bin/console vgr:shared:dispatch-message Core UpdatePlayerData 123</info>
  <info>php bin/console vgr:shared:dispatch-message Team UpdateTeamRank 456</info>
  <info>php bin/console vgr:shared:dispatch-message Badge UpdatePlayerBadge 789</info>

The command will automatically find the message class in the specified bounded context Message directory.
            ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $context = $input->getArgument('context');
        $messageName = $input->getArgument('message-name');
        $id = (int) $input->getArgument('id');

        try {
            // Find the message class
            $messageClass = $this->findMessageClass($context, $messageName);

            if (!$messageClass) {
                $io->error("Message class '{$messageName}' not found in {$context} context");
                $this->listAvailableMessages($io, $context);
                return Command::FAILURE;
            }

            // Verify the class exists and is instantiable
            if (!class_exists($messageClass)) {
                $io->error("Class '{$messageClass}' does not exist");
                return Command::FAILURE;
            }

            // Create reflection to check constructor
            $reflectionClass = new ReflectionClass($messageClass);
            $constructor = $reflectionClass->getConstructor();

            if (!$constructor) {
                $io->error("Message class '{$messageClass}' has no constructor");
                return Command::FAILURE;
            }

            // Check if constructor accepts the ID parameter
            $parameters = $constructor->getParameters();
            if (empty($parameters)) {
                $io->error("Message class '{$messageClass}' constructor requires no parameters");
                return Command::FAILURE;
            }

            // Instantiate the message
            $message = new $messageClass($id);

            // Dispatch the message
            $this->bus->dispatch($message);

            $io->success("Message '{$messageName}' dispatched successfully with ID: {$id}");

            return Command::SUCCESS;
        } catch (ReflectionException $e) {
            $io->error("Reflection error: " . $e->getMessage());
            return Command::FAILURE;
        } catch (ExceptionInterface $e) {
            $io->error("Messenger error: " . $e->getMessage());
            return Command::FAILURE;
        } catch (\Exception $e) {
            $io->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function findMessageClass(string $context, string $messageName): ?string
    {
        $contextDir = $this->getContextDirectory($context);

        if (!$contextDir) {
            return null;
        }

        $messageDir = $contextDir . '/Application/Message';

        if (!is_dir($messageDir)) {
            return null;
        }

        $finder = new Finder();
        $finder->files()->in($messageDir)->name('*.php');

        foreach ($finder as $file) {
            $className = $file->getBasename('.php');

            if ($className === $messageName) {
                // Build the full class name with namespace
                $relativePath = $file->getRelativePathname();
                $namespacePath = str_replace(['/', '.php'], ['\\', ''], $relativePath);

                return "App\\BoundedContext\\VideoGamesRecords\\{$context}\\Application\\Message\\{$namespacePath}";
            }
        }

        return null;
    }

    private function listAvailableMessages(SymfonyStyle $io, string $context): void
    {
        $contextDir = $this->getContextDirectory($context);

        if (!$contextDir) {
            $io->note("Context '{$context}' not found");
            $this->listAvailableContexts($io);
            return;
        }

        $messageDir = $contextDir . '/Application/Message';

        if (!is_dir($messageDir)) {
            $io->note("Message directory not found: {$messageDir}");
            return;
        }

        $finder = new Finder();
        $finder->files()->in($messageDir)->name('*.php');

        $messages = [];
        foreach ($finder as $file) {
            $className = $file->getBasename('.php');
            $relativePath = $file->getRelativePath();

            $category = $relativePath ? str_replace('/', ' > ', $relativePath) : 'Root';
            $messages[$category][] = $className;
        }

        if (empty($messages)) {
            $io->note("No message classes found in {$messageDir}");
            return;
        }

        $io->section("Available messages in {$context} context:");
        foreach ($messages as $category => $classList) {
            $io->writeln("<info>{$category}:</info>");
            foreach ($classList as $className) {
                $io->writeln("  - {$className}");
            }
        }
    }

    private function listAvailableContexts(SymfonyStyle $io): void
    {
        $vgrDir = $this->kernel->getProjectDir() . '/src/BoundedContext/VideoGamesRecords';

        if (!is_dir($vgrDir)) {
            $io->note("VideoGamesRecords directory not found");
            return;
        }

        $finder = new Finder();
        $finder->directories()->in($vgrDir)->depth(0);

        $contexts = [];
        foreach ($finder as $dir) {
            $contextName = $dir->getBasename();
            if ($contextName !== 'Shared') {
                $contexts[] = $contextName;
            }
        }

        if (!empty($contexts)) {
            $io->section("Available contexts:");
            foreach ($contexts as $contextName) {
                $io->writeln("  - {$contextName}");
            }
        }
    }

    private function getContextDirectory(string $context): ?string
    {
        $contextDir = $this->kernel->getProjectDir() . "/src/BoundedContext/VideoGamesRecords/{$context}";

        return is_dir($contextDir) ? $contextDir : null;
    }
}
