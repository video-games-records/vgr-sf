<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\GameMapper;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\GameStatus;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class GameMapperTest extends TestCase
{
    private GameMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new GameMapper('https://cdn.example.com');
    }

    private function makeGame(bool $withSerie = false, bool $withPicture = true): Game
    {
        $game = $this->createMock(Game::class);
        $game->method('getId')->willReturn(1);
        $game->method('getName')->willReturn('Super Mario');
        $game->method('getPicture')->willReturn($withPicture ? 'mario.png' : null);
        $game->method('getStatus')->willReturn(GameStatus::ACTIVE);
        $game->method('getPublishedAt')->willReturn(new DateTime('2023-01-01'));
        $game->method('getIsRank')->willReturn(true);
        $game->method('getNbChart')->willReturn(50);
        $game->method('getNbPost')->willReturn(10);
        $game->method('getNbPlayer')->willReturn(200);
        $game->method('getNbTeam')->willReturn(5);
        $game->method('getReleaseDate')->willReturn(new DateTime('1985-09-13'));
        $game->method('getSlug')->willReturn('super-mario');
        $game->method('getLastUpdate')->willReturn(new DateTime());
        $game->method('getSerie')->willReturn(null);
        $game->method('getPlatforms')->willReturn(new ArrayCollection([]));
        $game->method('getGenres')->willReturn(new ArrayCollection([]));

        return $game;
    }

    public function testToDTOMapsScalarFields(): void
    {
        $dto = $this->mapper->toDTO($this->makeGame());

        $this->assertSame(1, $dto->id);
        $this->assertSame('Super Mario', $dto->name);
        $this->assertSame('ACTIVE', $dto->status);
        $this->assertTrue($dto->isRank);
        $this->assertSame(50, $dto->nbChart);
        $this->assertSame(10, $dto->nbPost);
        $this->assertSame(200, $dto->nbPlayer);
        $this->assertSame(5, $dto->nbTeam);
        $this->assertSame('super-mario', $dto->slug);
    }

    public function testToDTOBuildsPictureUrlWithStoragePrefix(): void
    {
        $dto = $this->mapper->toDTO($this->makeGame());

        $this->assertSame('https://cdn.example.com/game/mario.png', $dto->picture);
    }

    public function testToDTOFallsBackToDefaultPictureWhenNull(): void
    {
        $dto = $this->mapper->toDTO($this->makeGame(withPicture: false));

        $this->assertSame('https://cdn.example.com/game/default.png', $dto->picture);
    }

    public function testToDTOWithNoSerieReturnsNullSerie(): void
    {
        $dto = $this->mapper->toDTO($this->makeGame());

        $this->assertNull($dto->serie);
    }

    public function testToDTOWithNoPlatformsReturnsEmptyArray(): void
    {
        $dto = $this->mapper->toDTO($this->makeGame());

        $this->assertSame([], $dto->platforms);
    }

    public function testToDTOWithNoGenresReturnsEmptyArray(): void
    {
        $dto = $this->mapper->toDTO($this->makeGame());

        $this->assertSame([], $dto->genres);
    }
}
