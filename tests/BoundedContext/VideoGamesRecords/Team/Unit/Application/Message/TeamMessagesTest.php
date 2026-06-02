<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Team\Unit\Application\Message;

use App\BoundedContext\VideoGamesRecords\Team\Application\Message\UpdateTeamChartRank;
use App\BoundedContext\VideoGamesRecords\Team\Application\Message\UpdateTeamData;
use App\BoundedContext\VideoGamesRecords\Team\Application\Message\UpdateTeamGameRank;
use App\BoundedContext\VideoGamesRecords\Team\Application\Message\UpdateTeamGroupRank;
use App\BoundedContext\VideoGamesRecords\Team\Application\Message\UpdateTeamRank;
use App\BoundedContext\VideoGamesRecords\Team\Application\Message\UpdateTeamSerieRank;
use PHPUnit\Framework\TestCase;

class TeamMessagesTest extends TestCase
{
    public function testUpdateTeamDataHoldsTeamId(): void
    {
        $message = new UpdateTeamData(42);

        $this->assertSame(42, $message->getTeamId());
        $this->assertSame('UpdateTeamData42', $message->getUniqueIdentifier());
    }

    public function testUpdateTeamChartRankHoldsChartId(): void
    {
        $message = new UpdateTeamChartRank(7);

        $this->assertSame(7, $message->getChartId());
        $this->assertSame('UpdateTeamChartRank7', $message->getUniqueIdentifier());
    }

    public function testUpdateTeamGameRankHoldsGameId(): void
    {
        $message = new UpdateTeamGameRank(3);

        $this->assertSame(3, $message->getGameId());
        $this->assertSame('UpdateTeamGameRank3', $message->getUniqueIdentifier());
    }

    public function testUpdateTeamGroupRankHoldsGroupId(): void
    {
        $message = new UpdateTeamGroupRank(5);

        $this->assertSame(5, $message->getGroupId());
        $this->assertSame('UpdateTeamGroupRank5', $message->getUniqueIdentifier());
    }

    public function testUpdateTeamSerieRankHoldsSerieId(): void
    {
        $message = new UpdateTeamSerieRank(9);

        $this->assertSame(9, $message->getSerieId());
        $this->assertSame('UpdateTeamSerieRank9', $message->getUniqueIdentifier());
    }

    public function testUpdateTeamRankHasUniqueIdentifier(): void
    {
        $message = new UpdateTeamRank();

        $this->assertSame('UpdateTeamRank', $message->getUniqueIdentifier());
    }

    public function testUniqueIdentifiersAreDifferentForDifferentIds(): void
    {
        $m1 = new UpdateTeamData(1);
        $m2 = new UpdateTeamData(2);

        $this->assertNotSame($m1->getUniqueIdentifier(), $m2->getUniqueIdentifier());
    }
}
