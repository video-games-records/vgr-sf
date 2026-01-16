<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\MessageHandler\Player;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerRank;
use App\BoundedContext\VideoGamesRecords\Shared\Domain\Tools\RankingTools;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;

#[AsMessageHandler]
readonly class UpdatePlayerRankHandler
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function __invoke(UpdatePlayerRank $updatePlayerChartRank): void
    {
        $this->majRankPointChart();
        $this->majRankPointGame();
        $this->majRankCup();
        $this->majRankMedal();
        $this->majRankBadge();
        $this->majRankProof();
    }

    public function majRankPointChart(): void
    {
        $players = $this->getPlayerRepository()->findBy([], ['pointChart' => 'DESC']);
        RankingTools::addObjectRank($players);
        $this->em->flush();
    }

    public function majRankPointGame(): void
    {
        $players = $this->getPlayerRepository()->findBy([], ['pointGame' => 'DESC']);
        RankingTools::addObjectRank($players, 'rankPointGame', ['pointGame']);
        $this->em->flush();
    }

    public function majRankMedal(): void
    {
        $players = $this->getPlayerRepository()->findBy([], ['chartRank0' => 'DESC', 'chartRank1' => 'DESC', 'chartRank2' => 'DESC', 'chartRank3' => 'DESC']);
        RankingTools::addObjectRank($players, 'rankMedal', ['chartRank0', 'chartRank1', 'chartRank2', 'chartRank3']);
        $this->em->flush();
    }

    public function majRankCup(): void
    {
        $players = $this->getPlayerRepository()->findBy([], ['gameRank0' => 'DESC', 'gameRank1' => 'DESC', 'gameRank2' => 'DESC', 'gameRank3' => 'DESC']);
        RankingTools::addObjectRank($players, 'rankCup', ['gameRank0', 'gameRank1', 'gameRank2', 'gameRank3']);
        $this->em->flush();
    }

    public function majRankProof(): void
    {
        $players = $this->getPlayerRepository()->findBy([], ['nbChartProven' => 'DESC']);
        RankingTools::addObjectRank($players, 'rankProof', ['nbChartProven']);
        $this->em->flush();
    }

    public function majRankBadge(): void
    {
        $players = $this->getPlayerRepository()->findBy([], ['pointBadge' => 'DESC', 'nbMasterBadge' => 'DESC']);
        RankingTools::addObjectRank($players, 'rankBadge', ['pointBadge', 'nbMasterBadge']);
        $this->em->flush();
    }

    private function getPlayerRepository(): EntityRepository
    {
        return $this->em->getRepository(Player::class);
    }
}
