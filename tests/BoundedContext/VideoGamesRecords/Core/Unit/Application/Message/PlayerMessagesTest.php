<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Application\Message;

use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerChartRank;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerCountryRank;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerData;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerGameRank;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerGroupRank;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerPlatformRank;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerRank;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerSerieRank;
use PHPUnit\Framework\TestCase;

class PlayerMessagesTest extends TestCase
{
    // ------------------------------------------------------------------
    // UpdatePlayerChartRank
    // ------------------------------------------------------------------

    public function testUpdatePlayerChartRankGetters(): void
    {
        $msg = new UpdatePlayerChartRank(42);
        $this->assertSame(42, $msg->getChartId());
        $this->assertSame('UpdatePlayerChartRank42', $msg->getUniqueIdentifier());
    }

    // ------------------------------------------------------------------
    // UpdatePlayerCountryRank
    // ------------------------------------------------------------------

    public function testUpdatePlayerCountryRankGetters(): void
    {
        $msg = new UpdatePlayerCountryRank(7);
        $this->assertSame(7, $msg->getCountryId());
        $this->assertSame('UpdatePlayerCountryRank7', $msg->getUniqueIdentifier());
    }

    // ------------------------------------------------------------------
    // UpdatePlayerData
    // ------------------------------------------------------------------

    public function testUpdatePlayerDataGetters(): void
    {
        $msg = new UpdatePlayerData(99);
        $this->assertSame(99, $msg->getPlayerId());
        $this->assertSame('UpdatePlayerData99', $msg->getUniqueIdentifier());
    }

    // ------------------------------------------------------------------
    // UpdatePlayerGameRank
    // ------------------------------------------------------------------

    public function testUpdatePlayerGameRankGetters(): void
    {
        $msg = new UpdatePlayerGameRank(15);
        $this->assertSame(15, $msg->getGameId());
        $this->assertSame('UpdatePlayerGameRank15', $msg->getUniqueIdentifier());
    }

    // ------------------------------------------------------------------
    // UpdatePlayerGroupRank
    // ------------------------------------------------------------------

    public function testUpdatePlayerGroupRankGetters(): void
    {
        $msg = new UpdatePlayerGroupRank(3);
        $this->assertSame(3, $msg->getGroupId());
        $this->assertSame('UpdatePlayerGroupRank3', $msg->getUniqueIdentifier());
    }

    // ------------------------------------------------------------------
    // UpdatePlayerPlatformRank
    // ------------------------------------------------------------------

    public function testUpdatePlayerPlatformRankGetters(): void
    {
        $msg = new UpdatePlayerPlatformRank(2);
        $this->assertSame(2, $msg->getPlatformId());
        $this->assertSame('UpdatePlayerPlatformRank2', $msg->getUniqueIdentifier());
    }

    // ------------------------------------------------------------------
    // UpdatePlayerRank (no constructor params)
    // ------------------------------------------------------------------

    public function testUpdatePlayerRankUniqueIdentifier(): void
    {
        $msg = new UpdatePlayerRank();
        $this->assertSame('UpdatePlayerRank', $msg->getUniqueIdentifier());
    }

    // ------------------------------------------------------------------
    // UpdatePlayerSerieRank
    // ------------------------------------------------------------------

    public function testUpdatePlayerSerieRankGetters(): void
    {
        $msg = new UpdatePlayerSerieRank(8);
        $this->assertSame(8, $msg->getSerieId());
        $this->assertSame('UpdatePlayerSerieRank8', $msg->getUniqueIdentifier());
    }

    // ------------------------------------------------------------------
    // Unique identifiers are unique across different IDs
    // ------------------------------------------------------------------

    public function testUniqueIdentifiersAreDifferentAcrossIds(): void
    {
        $msg1 = new UpdatePlayerChartRank(1);
        $msg2 = new UpdatePlayerChartRank(2);

        $this->assertNotSame($msg1->getUniqueIdentifier(), $msg2->getUniqueIdentifier());
    }
}
