<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Command;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'vgr:player-chart:deactivate-investigations',
    description: 'Set player charts in investigation for more than 14 days to unproved'
)]
class DeactivateExpiredInvestigationsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $date = new DateTime();
        $date->modify('-14 days');

        $playerCharts = $this->em->createQueryBuilder()
            ->select('pc')
            ->from('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart', 'pc')
            ->where('pc.status = :status')
            ->andWhere('pc.dateInvestigation < :date')
            ->setParameter('status', PlayerChartStatusEnum::REQUEST_PROOF_SENT)
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->getResult();

        foreach ($playerCharts as $playerChart) {
            $playerChart->setStatus(PlayerChartStatusEnum::UNPROVED);
        }

        $this->em->flush();

        $io->success(sprintf('%d score(s) set to unproved.', count($playerCharts)));

        return Command::SUCCESS;
    }
}
