<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\PlayerGameMapper;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerGame;
use DateTime;
use PHPUnit\Framework\TestCase;

class PlayerGameMapperTest extends TestCase
{
    private PlayerGameMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new PlayerGameMapper('https://cdn.example.com');
    }

    private function makePlayerGame(?string $picture = 'cover.png'): PlayerGame
    {
        $game = $this->createMock(Game::class);
        $game->method('getId')->willReturn(5);
        $game->method('getName')->willReturn('Metroid');
        $game->method('getPicture')->willReturn($picture);
        $game->method('getSlug')->willReturn('metroid');

        $pg = $this->createMock(PlayerGame::class);
        $pg->method('getGame')->willReturn($game);
        $pg->method('getLastUpdate')->willReturn(new DateTime('2024-03-01'));
        $pg->method('getPointChart')->willReturn(300);
        $pg->method('getPointGame')->willReturn(50);
        $pg->method('getRankPointChart')->willReturn(1);
        $pg->method('getChartRank0')->willReturn(2);
        $pg->method('getChartRank1')->willReturn(4);
        $pg->method('getChartRank2')->willReturn(6);
        $pg->method('getChartRank3')->willReturn(8);
        $pg->method('getNbChart')->willReturn(30);
        $pg->method('getNbChartProven')->willReturn(25);

        return $pg;
    }

    public function testToDTOMapsScalarFields(): void
    {
        $dto = $this->mapper->toDTO($this->makePlayerGame());

        $this->assertSame(5, $dto->gameId);
        $this->assertSame('Metroid', $dto->gameName);
        $this->assertSame('metroid', $dto->gameSlug);
        $this->assertSame(300, $dto->pointChart);
        $this->assertSame(50, $dto->pointGame);
        $this->assertSame(1, $dto->rankPointChart);
        $this->assertSame(2, $dto->chartRank0);
        $this->assertSame(4, $dto->chartRank1);
        $this->assertSame(6, $dto->chartRank2);
        $this->assertSame(8, $dto->chartRank3);
        $this->assertSame(30, $dto->nbChart);
        $this->assertSame(25, $dto->nbChartProven);
    }

    public function testToDTOBuildsPictureUrlWithPrefix(): void
    {
        $dto = $this->mapper->toDTO($this->makePlayerGame('cover.png'));

        $this->assertSame('https://cdn.example.com/game/cover.png', $dto->gamePicture);
    }

    public function testToDTOFallsBackToDefaultPictureWhenNull(): void
    {
        $dto = $this->mapper->toDTO($this->makePlayerGame(null));

        $this->assertSame('https://cdn.example.com/game/default.png', $dto->gamePicture);
    }
}
