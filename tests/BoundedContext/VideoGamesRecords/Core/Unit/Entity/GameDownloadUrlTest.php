<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\GameDownloadUrl;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;
use PHPUnit\Framework\TestCase;

class GameDownloadUrlTest extends TestCase
{
    private GameDownloadUrl $entity;

    protected function setUp(): void
    {
        $this->entity = new GameDownloadUrl();
    }

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->entity->getId());
    }

    public function testUrlDefaultsToEmptyString(): void
    {
        $this->assertSame('', $this->entity->getUrl());
    }

    public function testSetAndGetUrl(): void
    {
        $result = $this->entity->setUrl('https://store.steampowered.com/app/1234');
        $this->assertSame('https://store.steampowered.com/app/1234', $this->entity->getUrl());
        $this->assertSame($this->entity, $result);
    }

    public function testSetAndGetGame(): void
    {
        $game = $this->createMock(Game::class);
        $result = $this->entity->setGame($game);
        $this->assertSame($game, $this->entity->getGame());
        $this->assertSame($this->entity, $result);
    }

    public function testSetAndGetPlatform(): void
    {
        $platform = $this->createMock(Platform::class);
        $result = $this->entity->setPlatform($platform);
        $this->assertSame($platform, $this->entity->getPlatform());
        $this->assertSame($this->entity, $result);
    }

    public function testToString(): void
    {
        $platform = $this->createMock(Platform::class);
        $platform->method('getName')->willReturn('Steam');

        $this->entity->setPlatform($platform);
        $this->entity->setUrl('https://store.steampowered.com/app/1234');

        $this->assertSame('Steam (https://store.steampowered.com/app/1234)', (string) $this->entity);
    }
}
