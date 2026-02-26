<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Application\Command;

use App\BoundedContext\VideoGamesRecords\Dwh\Application\Message\UpdateTeam;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'vgr:dwh:update-team',
    description: 'Dispatch UpdateTeam message to update team data in DWH'
)]
class UpdateTeamCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Dispatching UpdateTeam message...');

        $this->messageBus->dispatch(new UpdateTeam());

        $output->writeln('UpdateTeam message dispatched successfully.');

        return Command::SUCCESS;
    }
}
