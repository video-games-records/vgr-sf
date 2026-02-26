<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Application\Command;

use App\BoundedContext\VideoGamesRecords\Dwh\Application\Message\UpdateGame;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'vgr:dwh:update-game',
    description: 'Dispatch UpdateGame message to update game data in DWH'
)]
class UpdateGameCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Dispatching UpdateGame message...');

        $this->messageBus->dispatch(new UpdateGame());

        $output->writeln('UpdateGame message dispatched successfully.');

        return Command::SUCCESS;
    }
}
