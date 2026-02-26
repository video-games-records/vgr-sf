<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Application\Command;

use App\BoundedContext\VideoGamesRecords\Dwh\Application\Message\UpdatePlayer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'vgr:dwh:update-player',
    description: 'Dispatch UpdatePlayer message to update player data in DWH'
)]
class UpdatePlayerCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Dispatching UpdatePlayer message...');

        $this->messageBus->dispatch(new UpdatePlayer());

        $output->writeln('UpdatePlayer message dispatched successfully.');

        return Command::SUCCESS;
    }
}
