<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\ChartMapper;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;
use PHPUnit\Framework\TestCase;

class ChartMapperTest extends TestCase
{
    public function testToDTOMapsAllFields(): void
    {
        $group = $this->createMock(Group::class);
        $group->method('getId')->willReturn(3);
        $group->method('getName')->willReturn('World 1');
        $group->method('getSlug')->willReturn('world-1');

        $chart = $this->createMock(Chart::class);
        $chart->method('getId')->willReturn(10);
        $chart->method('getName')->willReturn('Speed Run');
        $chart->method('getNbPost')->willReturn(4);
        $chart->method('getIsDlc')->willReturn(false);
        $chart->method('getSlug')->willReturn('speed-run');
        $chart->method('getGroup')->willReturn($group);

        $dto = (new ChartMapper())->toDTO($chart);

        $this->assertSame(10, $dto->id);
        $this->assertSame('Speed Run', $dto->name);
        $this->assertSame(4, $dto->nbPost);
        $this->assertFalse($dto->isDlc);
        $this->assertSame('speed-run', $dto->slug);
    }

    public function testToDTOMapsGroupRelation(): void
    {
        $group = $this->createMock(Group::class);
        $group->method('getId')->willReturn(3);
        $group->method('getName')->willReturn('World 1');
        $group->method('getSlug')->willReturn('world-1');

        $chart = $this->createMock(Chart::class);
        $chart->method('getId')->willReturn(10);
        $chart->method('getName')->willReturn('Speed Run');
        $chart->method('getNbPost')->willReturn(0);
        $chart->method('getIsDlc')->willReturn(false);
        $chart->method('getSlug')->willReturn('speed-run');
        $chart->method('getGroup')->willReturn($group);

        $dto = (new ChartMapper())->toDTO($chart);

        $this->assertSame(3, $dto->group['id']);
        $this->assertSame('World 1', $dto->group['name']);
        $this->assertSame('world-1', $dto->group['slug']);
    }

    public function testToDTOHandlesNullNameWithEmptyString(): void
    {
        $group = $this->createMock(Group::class);
        $group->method('getId')->willReturn(1);
        $group->method('getName')->willReturn(null);
        $group->method('getSlug')->willReturn('group');

        $chart = $this->createMock(Chart::class);
        $chart->method('getId')->willReturn(1);
        $chart->method('getName')->willReturn(null);
        $chart->method('getNbPost')->willReturn(0);
        $chart->method('getIsDlc')->willReturn(false);
        $chart->method('getSlug')->willReturn('chart');
        $chart->method('getGroup')->willReturn($group);

        $dto = (new ChartMapper())->toDTO($chart);

        $this->assertSame('', $dto->name);
        $this->assertSame('', $dto->group['name']);
    }
}
