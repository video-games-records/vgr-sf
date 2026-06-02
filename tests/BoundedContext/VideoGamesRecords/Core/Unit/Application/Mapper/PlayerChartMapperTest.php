<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\PlayerChartMapper;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartLib;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartType;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChartLib;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class PlayerChartMapperTest extends TestCase
{
    private PlayerChartMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new PlayerChartMapper();
    }

    private function makePlayer(): Player
    {
        $player = $this->createMock(Player::class);
        $player->method('getId')->willReturn(42);
        $player->method('getPseudo')->willReturn('TestPlayer');
        $player->method('getSlug')->willReturn('testplayer');
        return $player;
    }

    private function makePlayerChart(bool $withPlatform = false, bool $withLibs = false): PlayerChart
    {
        $playerChart = $this->createMock(PlayerChart::class);
        $playerChart->method('getId')->willReturn(10);
        $playerChart->method('getRank')->willReturn(3);
        $playerChart->method('getPointChart')->willReturn(75);
        $playerChart->method('getPlayer')->willReturn($this->makePlayer());

        if ($withPlatform) {
            $platform = $this->createMock(Platform::class);
            $platform->method('getId')->willReturn(5);
            $platform->method('getName')->willReturn('PC');
            $platform->method('getSlug')->willReturn('pc');
            $playerChart->method('getPlatform')->willReturn($platform);
        } else {
            $playerChart->method('getPlatform')->willReturn(null);
        }

        if ($withLibs) {
            $type = $this->createMock(ChartType::class);
            $type->method('getMask')->willReturn('30~');

            $libChart = $this->createMock(ChartLib::class);
            $libChart->method('getName')->willReturn('Score');
            $libChart->method('getType')->willReturn($type);

            $lib = $this->createMock(PlayerChartLib::class);
            $lib->method('getLibChart')->willReturn($libChart);
            $lib->method('getValue')->willReturn('1000');

            $playerChart->method('getLibs')->willReturn(new ArrayCollection([$lib]));
        } else {
            $playerChart->method('getLibs')->willReturn(new ArrayCollection([]));
        }

        return $playerChart;
    }

    public function testToDTOMapsScalarFields(): void
    {
        $dto = $this->mapper->toDTO($this->makePlayerChart());

        $this->assertSame(10, $dto->id);
        $this->assertSame(3, $dto->rank);
        $this->assertSame(75, $dto->pointChart);
    }

    public function testToDTOMapsPlayerData(): void
    {
        $dto = $this->mapper->toDTO($this->makePlayerChart());

        $this->assertSame(42, $dto->player['id']);
        $this->assertSame('TestPlayer', $dto->player['pseudo']);
        $this->assertSame('testplayer', $dto->player['slug']);
    }

    public function testToDTOWithNoPlatformReturnsNull(): void
    {
        $dto = $this->mapper->toDTO($this->makePlayerChart(withPlatform: false));

        $this->assertNull($dto->platform);
    }

    public function testToDTOWithPlatformMapsPlatformData(): void
    {
        $dto = $this->mapper->toDTO($this->makePlayerChart(withPlatform: true));

        $this->assertNotNull($dto->platform);
        $this->assertSame(5, $dto->platform['id']);
        $this->assertSame('PC', $dto->platform['name']);
        $this->assertSame('pc', $dto->platform['slug']);
    }

    public function testToDTOWithNoLibsReturnsEmptyValues(): void
    {
        $dto = $this->mapper->toDTO($this->makePlayerChart(withLibs: false));

        $this->assertSame([], $dto->values);
    }

    public function testToDTOWithLibsMapsFormattedValues(): void
    {
        $dto = $this->mapper->toDTO($this->makePlayerChart(withLibs: true));

        $this->assertCount(1, $dto->values);
        $this->assertSame('Score', $dto->values[0]['libChartName']);
        $this->assertSame('1000', $dto->values[0]['value']);
    }
}
