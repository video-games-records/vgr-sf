<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\EventSubscriber;

use App\BoundedContext\VideoGamesRecords\Proof\Domain\Event\ProofAccepted;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class UpdateNbChartProvenSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProofAccepted::class => 'updateNbChartProven',
        ];
    }

    public function updateNbChartProven(ProofAccepted $event): void
    {
        $playerChart = $event->getProof()->getPlayerChart();

        if ($playerChart === null) {
            return;
        }

        $playerId = $playerChart->getPlayer()->getId();

        // 1. Update PlayerGroup: recalculate nb_chart_proven from PlayerChart
        $this->connection->executeStatement("
            UPDATE vgr_player_group pg
            INNER JOIN (
                SELECT pc.player_id, c.group_id, COUNT(pc.id) AS nb
                FROM vgr_player_chart pc
                INNER JOIN vgr_chart c ON c.id = pc.chart_id
                WHERE pc.player_id = :playerId
                AND pc.status = 'proved'
                GROUP BY pc.player_id, c.group_id
            ) sub ON sub.player_id = pg.player_id AND sub.group_id = pg.group_id
            SET pg.nb_chart_proven = sub.nb
            WHERE pg.player_id = :playerId
        ", ['playerId' => $playerId]);

        // 2. Update PlayerGame: recalculate nb_chart_proven from PlayerGroup
        $this->connection->executeStatement("
            UPDATE vgr_player_game pgm
            INNER JOIN (
                SELECT pg.player_id, g.game_id, SUM(pg.nb_chart_proven) AS nb
                FROM vgr_player_group pg
                INNER JOIN vgr_group g ON g.id = pg.group_id
                WHERE pg.player_id = :playerId
                GROUP BY pg.player_id, g.game_id
            ) sub ON sub.player_id = pgm.player_id AND sub.game_id = pgm.game_id
            SET pgm.nb_chart_proven = sub.nb
            WHERE pgm.player_id = :playerId
        ", ['playerId' => $playerId]);

        // 3. Update Player: recalculate nb_chart_proven from PlayerGame
        $this->connection->executeStatement("
            UPDATE vgr_player p
            INNER JOIN (
                SELECT player_id, SUM(nb_chart_proven) AS nb
                FROM vgr_player_game
                WHERE player_id = :playerId
                GROUP BY player_id
            ) sub ON sub.player_id = p.id
            SET p.nb_chart_proven = sub.nb
            WHERE p.id = :playerId
        ", ['playerId' => $playerId]);
    }
}
