<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\PlayerSerieMapper;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerSerie;
use PHPUnit\Framework\TestCase;

class PlayerSerieMapperTest extends TestCase
{
    public function testToDTOMapsAllFields(): void
    {
        $playerSerie = $this->createMock(PlayerSerie::class);
        $playerSerie->method('getChartRank0')->willReturn(3);
        $playerSerie->method('getChartRank1')->willReturn(7);
        $playerSerie->method('getChartRank2')->willReturn(12);
        $playerSerie->method('getChartRank3')->willReturn(5);
        $playerSerie->method('getRankPointChart')->willReturn(2);
        $playerSerie->method('getPointChart')->willReturn(1500);
        $playerSerie->method('getNbChart')->willReturn(27);
        $playerSerie->method('getNbChartProven')->willReturn(10);
        $playerSerie->method('getNbGame')->willReturn(4);

        $dto = (new PlayerSerieMapper())->toDTO($playerSerie);

        $this->assertSame(3, $dto->platinum);
        $this->assertSame(7, $dto->gold);
        $this->assertSame(12, $dto->silver);
        $this->assertSame(5, $dto->bronze);
        $this->assertSame(2, $dto->rank);
        $this->assertSame(1500, $dto->pointChart);
        $this->assertSame(27, $dto->nbChart);
        $this->assertSame(10, $dto->nbChartProven);
        $this->assertSame(4, $dto->nbGame);
    }
}
