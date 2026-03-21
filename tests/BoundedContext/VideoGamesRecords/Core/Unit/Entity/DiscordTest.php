<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Discord;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class DiscordTest extends TestCase
{
    private Discord $discord;

    protected function setUp(): void
    {
        $this->discord = new Discord();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesEmptyGamesCollection(): void
    {
        $discord = new Discord();
        $this->assertInstanceOf(Collection::class, $discord->getGames());
        $this->assertCount(0, $discord->getGames());
    }

    // ------------------------------------------------------------------
    // Basic properties
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->discord->getId());
    }

    public function testSetAndGetId(): void
    {
        $result = $this->discord->setId(1);
        $this->assertSame(1, $this->discord->getId());
        $this->assertSame($this->discord, $result);
    }

    public function testSetAndGetName(): void
    {
        $result = $this->discord->setName('VGR Official');
        $this->assertSame('VGR Official', $this->discord->getName());
        $this->assertSame($this->discord, $result);
    }

    public function testSetAndGetUrl(): void
    {
        $result = $this->discord->setUrl('https://discord.gg/example');
        $this->assertSame('https://discord.gg/example', $this->discord->getUrl());
        $this->assertSame($this->discord, $result);
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testToString(): void
    {
        $this->discord->setName('VGR Official');
        $this->assertSame('Discord [VGR Official]', (string) $this->discord);
    }

    // ------------------------------------------------------------------
    // Games collection
    // ------------------------------------------------------------------

    public function testAddGameDoesNotDuplicate(): void
    {
        $game = $this->createMock(Game::class);
        $game->method('addDiscord')->willReturnSelf();

        $this->discord->addGame($game);
        $this->discord->addGame($game);

        $this->assertCount(1, $this->discord->getGames());
    }

    public function testAddGameCallsAddDiscordOnGame(): void
    {
        $game = $this->createMock(Game::class);
        $game->expects($this->once())->method('addDiscord')->with($this->discord);

        $this->discord->addGame($game);
    }

    public function testRemoveGame(): void
    {
        $game = $this->createMock(Game::class);
        $game->method('addDiscord')->willReturnSelf();
        $game->method('removeDiscord')->willReturnSelf();

        $this->discord->addGame($game);
        $this->discord->removeGame($game);

        $this->assertCount(0, $this->discord->getGames());
    }

    public function testRemoveGameCallsRemoveDiscordOnGame(): void
    {
        $game = $this->createMock(Game::class);
        $game->method('addDiscord')->willReturnSelf();
        $game->expects($this->once())->method('removeDiscord')->with($this->discord);

        $this->discord->addGame($game);
        $this->discord->removeGame($game);
    }

    public function testRemoveNonExistentGameDoesNothing(): void
    {
        $game = $this->createMock(Game::class);
        $game->expects($this->never())->method('removeDiscord');

        $this->discord->removeGame($game);
        $this->assertCount(0, $this->discord->getGames());
    }
}
