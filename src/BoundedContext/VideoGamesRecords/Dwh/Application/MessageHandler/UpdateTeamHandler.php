<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Application\MessageHandler;

use App\BoundedContext\VideoGamesRecords\Dwh\Application\DataProvider\Core\TeamDataProvider;
use App\BoundedContext\VideoGamesRecords\Dwh\Application\Message\UpdateTeam;
use App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity\Team as DwhTeam;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class UpdateTeamHandler
{
    public function __construct(
        private TeamDataProvider $teamDataProvider,
        #[Autowire(service: 'doctrine.orm.dwh_entity_manager')]
        private EntityManagerInterface $em,
    ) {
    }

    public function __invoke(UpdateTeam $message): void
    {
        $date1 = new DateTime();
        $date1->sub(new DateInterval('P1D'));
        $date2 = new DateTime();

        $data1 = $this->teamDataProvider->getNbPostDay($date1, $date2);
        $list = $this->teamDataProvider->getData();

        foreach ($list as $row) {
            $idTeam = $row['id'];
            $dwhTeam = new DwhTeam();
            $dwhTeam->setDate($date1->format('Y-m-d'));
            $dwhTeam->setFromArray($row);
            $dwhTeam->setNbPostDay((isset($data1[$idTeam])) ? $data1[$idTeam] : 0);
            $this->em->persist($dwhTeam);
        }

        $this->em->flush();
    }
}
