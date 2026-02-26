<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Application\MessageHandler;

use App\BoundedContext\VideoGamesRecords\Dwh\Application\DataProvider\Core\GameDataProvider;
use App\BoundedContext\VideoGamesRecords\Dwh\Application\Message\UpdateGame;
use App\BoundedContext\VideoGamesRecords\Dwh\Domain\Entity\Game as DwhGame;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class UpdateGameHandler
{
    public function __construct(
        private GameDataProvider $gameDataProvider,
        #[Autowire(service: 'doctrine.orm.dwh_entity_manager')]
        private EntityManagerInterface $em,
    ) {
    }

    public function __invoke(UpdateGame $message): void
    {
        $date1 = new DateTime();
        $date1->sub(new DateInterval('P1D'));
        $date2 = new DateTime();

        $data1 = $this->gameDataProvider->getNbPostDay($date1, $date2);
        $games = $this->gameDataProvider->getData();

        foreach ($games as $game) {
            $id = $game->getId();
            $object = new DwhGame();
            $object->setDate($date1->format('Y-m-d'));
            $object->setFromArray(
                [
                    'id' => $game->getId(),
                    'nbPost' => $game->getNbPost(),
                ]
            );
            $object->setNbPostDay((isset($data1[$id])) ? $data1[$id] : 0);
            $this->em->persist($object);
        }
        $this->em->flush();
    }
}
