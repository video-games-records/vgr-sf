<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\ChartFormDataMapper;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartLib;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartType;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChartLib;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class ChartFormDataMapperTest extends TestCase
{
    private ChartFormDataMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ChartFormDataMapper();
    }

    private function makeChartType(): ChartType
    {
        $type = $this->createMock(ChartType::class);
        $type->method('getId')->willReturn(1);
        $type->method('getMask')->willReturn('%d');
        $type->method('getParseMask')->willReturn([['key' => 'value']]);
        return $type;
    }

    private function makeChart(bool $withGroup = false): Chart
    {
        $type = $this->makeChartType();

        $lib = $this->createMock(ChartLib::class);
        $lib->method('getId')->willReturn(10);
        $lib->method('getName')->willReturn('Score');
        $lib->method('getType')->willReturn($type);

        $chart = $this->createMock(Chart::class);
        $chart->method('getId')->willReturn(5);
        $chart->method('getName')->willReturn('Speed Run');
        $chart->method('getSlug')->willReturn('speed-run');
        $chart->method('getIsProofVideoOnly')->willReturn(false);
        $chart->method('getLibs')->willReturn(new ArrayCollection([$lib]));

        if ($withGroup) {
            $group = $this->createMock(Group::class);
            $group->method('getId')->willReturn(3);
            $group->method('getName')->willReturn('World 1');
            $group->method('getSlug')->willReturn('world-1');
            $chart->method('getGroup')->willReturn($group);
        }

        return $chart;
    }

    private function makePlayerChart(?Platform $platform = null): PlayerChart
    {
        $lib = $this->createMock(PlayerChartLib::class);
        $lib->method('getId')->willReturn(99);
        $lib->method('getValue')->willReturn('5000');
        $lib->method('getParseValue')->willReturn(['value' => '5000']);

        $libChart = $this->createMock(ChartLib::class);
        $libChart->method('getId')->willReturn(10);
        $lib->method('getLibChart')->willReturn($libChart);

        $playerChart = $this->createMock(PlayerChart::class);
        $playerChart->method('getId')->willReturn(77);
        $playerChart->method('getRank')->willReturn(2);
        $playerChart->method('getPointChart')->willReturn(90);
        $playerChart->method('getStatus')->willReturn(PlayerChartStatusEnum::NONE);
        $playerChart->method('getPlatform')->willReturn($platform);
        $playerChart->method('getLastUpdate')->willReturn(new DateTime('2024-06-01'));
        $playerChart->method('getLibs')->willReturn(new ArrayCollection([$lib]));

        return $playerChart;
    }

    public function testToDTOMapsChartScalarFields(): void
    {
        $dto = $this->mapper->toDTO($this->makeChart(), null);

        $this->assertSame(5, $dto->id);
        $this->assertSame('Speed Run', $dto->name);
        $this->assertSame('speed-run', $dto->slug);
        $this->assertFalse($dto->isProofVideoOnly);
    }

    public function testToDTOMapsLibs(): void
    {
        $dto = $this->mapper->toDTO($this->makeChart(), null);

        $this->assertCount(1, $dto->libs);
        $this->assertSame(10, $dto->libs[0]->id);
        $this->assertSame('Score', $dto->libs[0]->name);
    }

    public function testToDTOWithNullPlayerChartReturnsEmptyPlayerChart(): void
    {
        $dto = $this->mapper->toDTO($this->makeChart(), null);

        $this->assertSame(-1, $dto->playerChart->id);
        $this->assertNull($dto->playerChart->rank);
        $this->assertSame(0, $dto->playerChart->pointChart);
        $this->assertSame('none', $dto->playerChart->status);
    }

    public function testToDTOWithNullPlayerChartBuildsEmptyLibsFromParseMask(): void
    {
        $dto = $this->mapper->toDTO($this->makeChart(), null);

        $this->assertCount(1, $dto->playerChart->libs);
        $this->assertSame(-1, $dto->playerChart->libs[0]->id);
        $this->assertSame(10, $dto->playerChart->libs[0]->libChartId);
    }

    public function testToDTOWithPlayerChartMapsPlayerChartData(): void
    {
        $dto = $this->mapper->toDTO($this->makeChart(), $this->makePlayerChart());

        $this->assertSame(77, $dto->playerChart->id);
        $this->assertSame(2, $dto->playerChart->rank);
        $this->assertSame(90, $dto->playerChart->pointChart);
        $this->assertSame('none', $dto->playerChart->status);
        $this->assertNull($dto->playerChart->platform);
    }

    public function testToDTOWithoutGroupReturnsNullGroup(): void
    {
        $dto = $this->mapper->toDTO($this->makeChart(), null, includeGroup: false);

        $this->assertNull($dto->group);
    }

    public function testToDTOWithIncludeGroupMapsGroupData(): void
    {
        $dto = $this->mapper->toDTO($this->makeChart(withGroup: true), null, includeGroup: true);

        $this->assertNotNull($dto->group);
        $this->assertSame(3, $dto->group['id']);
        $this->assertSame('World 1', $dto->group['name']);
        $this->assertSame('world-1', $dto->group['slug']);
    }

    public function testToDTOWithPlayerChartPlatformMapsPlatformData(): void
    {
        $platform = $this->createMock(Platform::class);
        $platform->method('getId')->willReturn(8);
        $platform->method('getName')->willReturn('PlayStation');
        $platform->method('getSlug')->willReturn('playstation');

        $playerChart = $this->makePlayerChart($platform);

        $dto = $this->mapper->toDTO($this->makeChart(), $playerChart);

        $this->assertNotNull($dto->playerChart->platform);
        $this->assertSame(8, $dto->playerChart->platform['id']);
        $this->assertSame('PlayStation', $dto->playerChart->platform['name']);
    }
}
