<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Command;

use Doctrine\DBAL\Exception;
use App\BoundedContext\VideoGamesRecords\Core\Application\Manager\LostPositionManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'vgr:lost-position:purge',
    description: 'Purge old lost positions'
)]
class PurgeLostPositionsCommand extends Command
{
    public function __construct(
        private readonly LostPositionManager $lostPositionManager
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->lostPositionManager->purge();

        $io->success('Lost positions purged successfully.');

        return Command::SUCCESS;
    }
}
