<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\GroupMapper;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;
use PHPUnit\Framework\TestCase;

class GroupMapperTest extends TestCase
{
    private GroupMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new GroupMapper();
    }

    private function makeGroup(
        int $id = 2,
        ?string $name = 'World 1',
        int $nbChart = 5,
        int $nbPost = 0,
        int $nbPlayer = 10,
        bool $isRank = true,
        bool $isDlc = false,
        string $slug = 'world-1',
        int $gameId = 1,
        string $gameName = 'Super Mario',
        string $gameSlug = 'super-mario',
    ): Group {
        $game = $this->createMock(Game::class);
        $game->method('getId')->willReturn($gameId);
        $game->method('getName')->willReturn($gameName);
        $game->method('getSlug')->willReturn($gameSlug);

        $group = $this->createMock(Group::class);
        $group->method('getId')->willReturn($id);
        $group->method('getName')->willReturn($name);
        $group->method('getNbChart')->willReturn($nbChart);
        $group->method('getNbPost')->willReturn($nbPost);
        $group->method('getNbPlayer')->willReturn($nbPlayer);
        $group->method('getIsRank')->willReturn($isRank);
        $group->method('getIsDlc')->willReturn($isDlc);
        $group->method('getSlug')->willReturn($slug);
        $group->method('getGame')->willReturn($game);

        return $group;
    }

    public function testToDTOMapsAllFields(): void
    {
        $dto = $this->mapper->toDTO($this->makeGroup());

        $this->assertSame(2, $dto->id);
        $this->assertSame('World 1', $dto->name);
        $this->assertSame(5, $dto->nbChart);
        $this->assertSame(0, $dto->nbPost);
        $this->assertSame(10, $dto->nbPlayer);
        $this->assertTrue($dto->isRank);
        $this->assertFalse($dto->isDlc);
        $this->assertSame('world-1', $dto->slug);
    }

    public function testToDTOMapsGameRelation(): void
    {
        $dto = $this->mapper->toDTO($this->makeGroup());

        $this->assertSame(1, $dto->game['id']);
        $this->assertSame('Super Mario', $dto->game['name']);
        $this->assertSame('super-mario', $dto->game['slug']);
    }

    public function testToDTOHandlesNullNameWithEmptyString(): void
    {
        $dto = $this->mapper->toDTO($this->makeGroup(name: null));

        $this->assertSame('', $dto->name);
    }
}
