<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Shared\Unit\Domain\Tools;

use App\BoundedContext\VideoGamesRecords\Shared\Domain\Tools\RankingTools;
use PHPUnit\Framework\TestCase;

class RankingToolsTest extends TestCase
{
    // ------------------------------------------------------------------
    // order
    // ------------------------------------------------------------------

    public function testOrderReturnsEmptyArrayUnchanged(): void
    {
        $this->assertSame([], RankingTools::order([], ['score' => SORT_DESC]));
    }

    public function testOrderSortsDescending(): void
    {
        $data = [
            ['score' => 10],
            ['score' => 50],
            ['score' => 20],
        ];

        $result = RankingTools::order($data, ['score' => SORT_DESC]);

        $this->assertSame(50, $result[0]['score']);
        $this->assertSame(20, $result[1]['score']);
        $this->assertSame(10, $result[2]['score']);
    }

    public function testOrderSortsAscending(): void
    {
        $data = [
            ['score' => 30],
            ['score' => 10],
            ['score' => 20],
        ];

        $result = RankingTools::order($data, ['score' => SORT_ASC]);

        $this->assertSame(10, $result[0]['score']);
        $this->assertSame(20, $result[1]['score']);
        $this->assertSame(30, $result[2]['score']);
    }

    public function testOrderThrowsWhenColumnMissing(): void
    {
        $data = [
            ['score' => 10],
            ['score' => 20],
        ];

        $this->expectException(\InvalidArgumentException::class);
        RankingTools::order($data, ['missing_column' => SORT_DESC]);
    }

    // ------------------------------------------------------------------
    // addRank
    // ------------------------------------------------------------------

    public function testAddRankOnEmptyArrayReturnsEmpty(): void
    {
        $this->assertSame([], RankingTools::addRank([], 'rank', ['pointChart']));
    }

    public function testAddRankStartsAtOne(): void
    {
        $data = [['pointChart' => 100], ['pointChart' => 50]];

        $result = RankingTools::addRank($data);

        $this->assertSame(1, $result[0]['rank']);
    }

    public function testAddRankIncrementsForDifferentScores(): void
    {
        $data = [
            ['pointChart' => 100],
            ['pointChart' => 80],
            ['pointChart' => 60],
        ];

        $result = RankingTools::addRank($data);

        $this->assertSame(1, $result[0]['rank']);
        $this->assertSame(2, $result[1]['rank']);
        $this->assertSame(3, $result[2]['rank']);
    }

    public function testAddRankSkipsRankForTies(): void
    {
        $data = [
            ['pointChart' => 100],
            ['pointChart' => 100],
            ['pointChart' => 50],
        ];

        $result = RankingTools::addRank($data);

        $this->assertSame(1, $result[0]['rank']);
        $this->assertSame(1, $result[1]['rank']);
        $this->assertSame(3, $result[2]['rank']); // jumps from 1 to 3
    }

    public function testAddRankWithCustomKeyName(): void
    {
        $data = [['pointChart' => 100]];

        $result = RankingTools::addRank($data, 'myRank');

        $this->assertArrayHasKey('myRank', $result[0]);
        $this->assertSame(1, $result[0]['myRank']);
    }

    // ------------------------------------------------------------------
    // addObjectRank
    // ------------------------------------------------------------------

    public function testAddObjectRankSetsRankOnObjects(): void
    {
        $obj1 = new class
        {
            public int $rank = 0;

            public function getPointChart(): int
            {
                return 100;
            }

            public function setRankPointChart(int $v): void
            {
                $this->rank = $v;
            }
        };
        $obj2 = new class
        {
            public int $rank = 0;

            public function getPointChart(): int
            {
                return 50;
            }

            public function setRankPointChart(int $v): void
            {
                $this->rank = $v;
            }
        };

        RankingTools::addObjectRank([$obj1, $obj2]);

        $this->assertSame(1, $obj1->rank);
        $this->assertSame(2, $obj2->rank);
    }

    public function testAddObjectRankHandlesTies(): void
    {
        $obj1 = new class
        {
            public int $rank = 0;

            public function getPointChart(): int
            {
                return 100;
            }

            public function setRankPointChart(int $v): void
            {
                $this->rank = $v;
            }
        };
        $obj2 = new class
        {
            public int $rank = 0;

            public function getPointChart(): int
            {
                return 100;
            }

            public function setRankPointChart(int $v): void
            {
                $this->rank = $v;
            }
        };
        $obj3 = new class
        {
            public int $rank = 0;

            public function getPointChart(): int
            {
                return 50;
            }

            public function setRankPointChart(int $v): void
            {
                $this->rank = $v;
            }
        };

        RankingTools::addObjectRank([$obj1, $obj2, $obj3]);

        $this->assertSame(1, $obj1->rank);
        $this->assertSame(1, $obj2->rank);
        $this->assertSame(3, $obj3->rank);
    }

    // ------------------------------------------------------------------
    // chartPointProvider
    // ------------------------------------------------------------------

    public function testChartPointProviderReturnsOneEntryForOneParticipant(): void
    {
        $points = RankingTools::chartPointProvider(1);

        $this->assertCount(1, $points);
        $this->assertArrayHasKey(1, $points);
    }

    public function testChartPointProviderFirstPlaceHasHighestPoints(): void
    {
        $points = RankingTools::chartPointProvider(10);

        $this->assertGreaterThan($points[2], $points[1]);
    }

    public function testChartPointProviderPointsAreDecreasing(): void
    {
        $points = RankingTools::chartPointProvider(5);

        for ($i = 1; $i < 5; $i++) {
            $this->assertGreaterThanOrEqual($points[$i + 1], $points[$i]);
        }
    }

    public function testChartPointProviderAndPlatformPointProviderReturnSameResult(): void
    {
        $chartPoints = RankingTools::chartPointProvider(10);
        $platformPoints = RankingTools::platformPointProvider(10);

        $this->assertSame($chartPoints, $platformPoints);
    }
}
