<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\LatestScoreMapper;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartLib;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartType;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChartLib;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class LatestScoreMapperTest extends TestCase
{
    private LatestScoreMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new LatestScoreMapper();
    }

    private function makePlayerChart(bool $withPlatform = false, bool $withLibs = false): PlayerChart
    {
        $game = $this->createMock(Game::class);
        $game->method('getId')->willReturn(1);
        $game->method('getName')->willReturn('Zelda');
        $game->method('getSlug')->willReturn('zelda');

        $group = $this->createMock(Group::class);
        $group->method('getId')->willReturn(2);
        $group->method('getName')->willReturn('Overworld');
        $group->method('getSlug')->willReturn('overworld');
        $group->method('getGame')->willReturn($game);

        $chart = $this->createMock(Chart::class);
        $chart->method('getId')->willReturn(3);
        $chart->method('getName')->willReturn('Boss Rush');
        $chart->method('getSlug')->willReturn('boss-rush');
        $chart->method('getGroup')->willReturn($group);

        $player = $this->createMock(Player::class);
        $player->method('getId')->willReturn(9);
        $player->method('getPseudo')->willReturn('Hero');
        $player->method('getSlug')->willReturn('hero');

        $playerChart = $this->createMock(PlayerChart::class);
        $playerChart->method('getId')->willReturn(20);
        $playerChart->method('getRank')->willReturn(1);
        $playerChart->method('getPointChart')->willReturn(100);
        $playerChart->method('getPlayer')->willReturn($player);
        $playerChart->method('getChart')->willReturn($chart);
        $playerChart->method('getLastUpdate')->willReturn(new DateTime('2024-01-15'));

        if ($withPlatform) {
            $platform = $this->createMock(Platform::class);
            $platform->method('getId')->willReturn(6);
            $platform->method('getName')->willReturn('Switch');
            $platform->method('getSlug')->willReturn('switch');
            $playerChart->method('getPlatform')->willReturn($platform);
        } else {
            $playerChart->method('getPlatform')->willReturn(null);
        }

        if ($withLibs) {
            $type = $this->createMock(ChartType::class);
            $type->method('getMask')->willReturn('30~');

            $libChart = $this->createMock(ChartLib::class);
            $libChart->method('getName')->willReturn('Time');
            $libChart->method('getType')->willReturn($type);

            $lib = $this->createMock(PlayerChartLib::class);
            $lib->method('getLibChart')->willReturn($libChart);
            $lib->method('getValue')->willReturn('2500');

            $playerChart->method('getLibs')->willReturn(new ArrayCollection([$lib]));
        } else {
            $playerChart->method('getLibs')->willReturn(new ArrayCollection([]));
        }

        return $playerChart;
    }

    public function testToDTOMapsScalarFields(): void
    {
        $dto = $this->mapper->toDTO($this->makePlayerChart());

        $this->assertSame(20, $dto->id);
        $this->assertSame(1, $dto->rank);
        $this->assertSame(100, $dto->pointChart);
    }

    public function testToDTOMapsPlayerData(): void
    {
        $dto = $this->mapper->toDTO($this->makePlayerChart());

        $this->assertSame(9, $dto->player['id']);
        $this->assertSame('Hero', $dto->player['pseudo']);
        $this->assertSame('hero', $dto->player['slug']);
    }

    public function testToDTOMapsChartHierarchy(): void
    {
        $dto = $this->mapper->toDTO($this->makePlayerChart());

        $this->assertSame(3, $dto->chart['id']);
        $this->assertSame('Boss Rush', $dto->chart['name']);
        $this->assertSame(2, $dto->chart['group']['id']);
        $this->assertSame('Overworld', $dto->chart['group']['name']);
        $this->assertSame(1, $dto->chart['group']['game']['id']);
        $this->assertSame('Zelda', $dto->chart['group']['game']['name']);
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
        $this->assertSame(6, $dto->platform['id']);
        $this->assertSame('Switch', $dto->platform['name']);
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
        $this->assertSame('Time', $dto->values[0]['libChartName']);
        $this->assertSame('2500', $dto->values[0]['value']);
    }
}
