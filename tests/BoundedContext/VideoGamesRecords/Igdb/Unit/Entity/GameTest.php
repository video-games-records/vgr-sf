<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Igdb\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\Genre;
use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\Platform;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    private Game $game;

    protected function setUp(): void
    {
        $this->game = new Game();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesEmptyCollections(): void
    {
        $game = new Game();

        $this->assertInstanceOf(Collection::class, $game->getGenres());
        $this->assertCount(0, $game->getGenres());
        $this->assertInstanceOf(Collection::class, $game->getPlatforms());
        $this->assertCount(0, $game->getPlatforms());
    }

    public function testConstructorInitializesTimestamps(): void
    {
        $before = new DateTimeImmutable();
        $game = new Game();
        $after = new DateTimeImmutable();

        $this->assertGreaterThanOrEqual($before, $game->getCreatedAt());
        $this->assertLessThanOrEqual($after, $game->getCreatedAt());
        $this->assertGreaterThanOrEqual($before, $game->getUpdatedAt());
        $this->assertLessThanOrEqual($after, $game->getUpdatedAt());
    }

    // ------------------------------------------------------------------
    // Basic properties getters / setters
    // ------------------------------------------------------------------

    public function testSetAndGetId(): void
    {
        $result = $this->game->setId(42);
        $this->assertSame(42, $this->game->getId());
        $this->assertSame($this->game, $result);
    }

    public function testSetAndGetName(): void
    {
        $result = $this->game->setName('The Legend of Zelda');
        $this->assertSame('The Legend of Zelda', $this->game->getName());
        $this->assertSame($this->game, $result);
    }

    public function testSlugDefaultsToNull(): void
    {
        $this->assertNull($this->game->getSlug());
    }

    public function testSetAndGetSlug(): void
    {
        $result = $this->game->setSlug('the-legend-of-zelda');
        $this->assertSame('the-legend-of-zelda', $this->game->getSlug());
        $this->assertSame($this->game, $result);
    }

    public function testSetSlugToNull(): void
    {
        $this->game->setSlug('some-slug');
        $this->game->setSlug(null);
        $this->assertNull($this->game->getSlug());
    }

    public function testStorylineDefaultsToNull(): void
    {
        $this->assertNull($this->game->getStoryline());
    }

    public function testSetAndGetStoryline(): void
    {
        $result = $this->game->setStoryline('A long time ago in a land far away...');
        $this->assertSame('A long time ago in a land far away...', $this->game->getStoryline());
        $this->assertSame($this->game, $result);
    }

    public function testSetStorylineToNull(): void
    {
        $this->game->setStoryline('Some storyline');
        $this->game->setStoryline(null);
        $this->assertNull($this->game->getStoryline());
    }

    public function testSummaryDefaultsToNull(): void
    {
        $this->assertNull($this->game->getSummary());
    }

    public function testSetAndGetSummary(): void
    {
        $result = $this->game->setSummary('A great adventure game.');
        $this->assertSame('A great adventure game.', $this->game->getSummary());
        $this->assertSame($this->game, $result);
    }

    public function testSetSummaryToNull(): void
    {
        $this->game->setSummary('Some summary');
        $this->game->setSummary(null);
        $this->assertNull($this->game->getSummary());
    }

    public function testUrlDefaultsToNull(): void
    {
        $this->assertNull($this->game->getUrl());
    }

    public function testSetAndGetUrl(): void
    {
        $result = $this->game->setUrl('https://www.igdb.com/games/zelda');
        $this->assertSame('https://www.igdb.com/games/zelda', $this->game->getUrl());
        $this->assertSame($this->game, $result);
    }

    public function testSetUrlToNull(): void
    {
        $this->game->setUrl('https://www.igdb.com/games/zelda');
        $this->game->setUrl(null);
        $this->assertNull($this->game->getUrl());
    }

    public function testChecksumDefaultsToNull(): void
    {
        $this->assertNull($this->game->getChecksum());
    }

    public function testSetAndGetChecksum(): void
    {
        $result = $this->game->setChecksum('abc123def456');
        $this->assertSame('abc123def456', $this->game->getChecksum());
        $this->assertSame($this->game, $result);
    }

    public function testSetChecksumToNull(): void
    {
        $this->game->setChecksum('abc123');
        $this->game->setChecksum(null);
        $this->assertNull($this->game->getChecksum());
    }

    public function testFirstReleaseDateDefaultsToNull(): void
    {
        $this->assertNull($this->game->getFirstReleaseDate());
    }

    public function testSetAndGetFirstReleaseDate(): void
    {
        $result = $this->game->setFirstReleaseDate(1000000000);
        $this->assertSame(1000000000, $this->game->getFirstReleaseDate());
        $this->assertSame($this->game, $result);
    }

    public function testSetFirstReleaseDateToNull(): void
    {
        $this->game->setFirstReleaseDate(1000000000);
        $this->game->setFirstReleaseDate(null);
        $this->assertNull($this->game->getFirstReleaseDate());
    }

    // ------------------------------------------------------------------
    // Timestamps
    // ------------------------------------------------------------------

    public function testSetAndGetCreatedAt(): void
    {
        $date = new DateTimeImmutable('2024-01-15 10:00:00');
        $result = $this->game->setCreatedAt($date);
        $this->assertSame($date, $this->game->getCreatedAt());
        $this->assertSame($this->game, $result);
    }

    public function testSetAndGetUpdatedAt(): void
    {
        $date = new DateTimeImmutable('2024-06-01 12:00:00');
        $result = $this->game->setUpdatedAt($date);
        $this->assertSame($date, $this->game->getUpdatedAt());
        $this->assertSame($this->game, $result);
    }

    // ------------------------------------------------------------------
    // versionParent relation
    // ------------------------------------------------------------------

    public function testVersionParentDefaultsToNull(): void
    {
        $this->assertNull($this->game->getVersionParent());
    }

    public function testSetAndGetVersionParent(): void
    {
        $parent = $this->createMock(Game::class);
        $result = $this->game->setVersionParent($parent);
        $this->assertSame($parent, $this->game->getVersionParent());
        $this->assertSame($this->game, $result);
    }

    public function testSetVersionParentToNull(): void
    {
        $parent = $this->createMock(Game::class);
        $this->game->setVersionParent($parent);
        $this->game->setVersionParent(null);
        $this->assertNull($this->game->getVersionParent());
    }

    // ------------------------------------------------------------------
    // Genre collection
    // ------------------------------------------------------------------

    public function testAddGenre(): void
    {
        $genre = $this->createMock(Genre::class);

        $result = $this->game->addGenre($genre);

        $this->assertCount(1, $this->game->getGenres());
        $this->assertTrue($this->game->getGenres()->contains($genre));
        $this->assertSame($this->game, $result);
    }

    public function testAddGenreDoesNotDuplicate(): void
    {
        $genre = $this->createMock(Genre::class);

        $this->game->addGenre($genre);
        $this->game->addGenre($genre);

        $this->assertCount(1, $this->game->getGenres());
    }

    public function testRemoveGenre(): void
    {
        $genre = $this->createMock(Genre::class);

        $this->game->addGenre($genre);
        $result = $this->game->removeGenre($genre);

        $this->assertCount(0, $this->game->getGenres());
        $this->assertFalse($this->game->getGenres()->contains($genre));
        $this->assertSame($this->game, $result);
    }

    public function testRemoveNonExistentGenreDoesNothing(): void
    {
        $genre = $this->createMock(Genre::class);

        $this->game->removeGenre($genre);

        $this->assertCount(0, $this->game->getGenres());
    }

    // ------------------------------------------------------------------
    // Platform collection
    // ------------------------------------------------------------------

    public function testAddPlatform(): void
    {
        $platform = $this->createMock(Platform::class);

        $result = $this->game->addPlatform($platform);

        $this->assertCount(1, $this->game->getPlatforms());
        $this->assertTrue($this->game->getPlatforms()->contains($platform));
        $this->assertSame($this->game, $result);
    }

    public function testAddPlatformDoesNotDuplicate(): void
    {
        $platform = $this->createMock(Platform::class);

        $this->game->addPlatform($platform);
        $this->game->addPlatform($platform);

        $this->assertCount(1, $this->game->getPlatforms());
    }

    public function testRemovePlatform(): void
    {
        $platform = $this->createMock(Platform::class);

        $this->game->addPlatform($platform);
        $result = $this->game->removePlatform($platform);

        $this->assertCount(0, $this->game->getPlatforms());
        $this->assertFalse($this->game->getPlatforms()->contains($platform));
        $this->assertSame($this->game, $result);
    }

    public function testRemoveNonExistentPlatformDoesNothing(): void
    {
        $platform = $this->createMock(Platform::class);

        $this->game->removePlatform($platform);

        $this->assertCount(0, $this->game->getPlatforms());
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testGetFirstReleaseDateAsDateTimeReturnsNullWhenNotSet(): void
    {
        $this->assertNull($this->game->getFirstReleaseDateAsDateTime());
    }

    public function testGetFirstReleaseDateAsDateTimeReturnsDateTimeImmutable(): void
    {
        $timestamp = 1000000000;
        $this->game->setFirstReleaseDate($timestamp);

        $result = $this->game->getFirstReleaseDateAsDateTime();

        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        $this->assertSame($timestamp, $result->getTimestamp());
    }

    public function testToString(): void
    {
        $this->game->setName('Final Fantasy VII');
        $this->assertSame('Final Fantasy VII', (string) $this->game);
    }
}
