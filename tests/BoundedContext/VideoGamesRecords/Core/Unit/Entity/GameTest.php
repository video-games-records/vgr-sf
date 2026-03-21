<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\MasterBadge;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Discord;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Platform;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Rule;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\GameStatus;
use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\Game as IgdbGame;
use App\BoundedContext\Forum\Domain\Entity\Forum;
use DateTime;
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

        $this->assertInstanceOf(Collection::class, $game->getGroups());
        $this->assertCount(0, $game->getGroups());

        $this->assertInstanceOf(Collection::class, $game->getPlatforms());
        $this->assertCount(0, $game->getPlatforms());

        $this->assertInstanceOf(Collection::class, $game->getRules());
        $this->assertCount(0, $game->getRules());

        $this->assertInstanceOf(Collection::class, $game->getDiscords());
        $this->assertCount(0, $game->getDiscords());

        $this->assertInstanceOf(Collection::class, $game->getPlayerGame());
        $this->assertCount(0, $game->getPlayerGame());

        $this->assertInstanceOf(Collection::class, $game->getTeamGame());
        $this->assertCount(0, $game->getTeamGame());
    }

    // ------------------------------------------------------------------
    // Basic properties
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->game->getId());
    }

    public function testSetAndGetId(): void
    {
        $result = $this->game->setId(1);
        $this->assertSame(1, $this->game->getId());
        $this->assertSame($this->game, $result);
    }

    public function testLibGameEnDefaultsToEmptyString(): void
    {
        $this->assertSame('', $this->game->getLibGameEn());
    }

    public function testSetAndGetLibGameEn(): void
    {
        $result = $this->game->setLibGameEn('Super Mario Bros');
        $this->assertSame('Super Mario Bros', $this->game->getLibGameEn());
        $this->assertSame($this->game, $result);
    }

    public function testLibGameFrDefaultsToEmptyString(): void
    {
        $this->assertSame('', $this->game->getLibGameFr());
    }

    public function testSetAndGetLibGameFr(): void
    {
        $result = $this->game->setLibGameFr('Super Mario Bros');
        $this->assertSame('Super Mario Bros', $this->game->getLibGameFr());
        $this->assertSame($this->game, $result);
    }

    public function testSetLibGameFrWithNullDoesNotChange(): void
    {
        $this->game->setLibGameFr('Super Mario Bros');
        $this->game->setLibGameFr(null);
        $this->assertSame('Super Mario Bros', $this->game->getLibGameFr());
    }

    public function testDownloadUrlDefaultsToNull(): void
    {
        $this->assertNull($this->game->getDownloadUrl());
    }

    public function testSetAndGetDownloadUrl(): void
    {
        $result = $this->game->setDownloadurl('https://example.com/download');
        $this->assertSame('https://example.com/download', $this->game->getDownloadUrl());
        $this->assertSame($this->game, $result);
    }

    public function testSetDownloadUrlToNull(): void
    {
        $this->game->setDownloadurl('https://example.com/download');
        $this->game->setDownloadurl(null);
        $this->assertNull($this->game->getDownloadUrl());
    }

    public function testStatusDefaultsToCreated(): void
    {
        $this->assertSame(GameStatus::CREATED, $this->game->getStatus());
    }

    public function testSetAndGetStatus(): void
    {
        $result = $this->game->setStatus(GameStatus::ACTIVE);
        $this->assertSame(GameStatus::ACTIVE, $this->game->getStatus());
        $this->assertSame($this->game, $result);
    }

    public function testGetGameStatus(): void
    {
        $this->game->setStatus(GameStatus::ACTIVE);
        $gameStatus = $this->game->getGameStatus();
        $this->assertInstanceOf(GameStatus::class, $gameStatus);
        $this->assertSame(GameStatus::ACTIVE, $gameStatus->getValue());
    }

    public function testGetStatusAsString(): void
    {
        $this->game->setStatus(GameStatus::COMPLETED);
        $this->assertSame(GameStatus::COMPLETED, $this->game->getStatusAsString());
    }

    public function testPublishedAtDefaultsToNull(): void
    {
        $this->assertNull($this->game->getPublishedAt());
    }

    public function testSetAndGetPublishedAt(): void
    {
        $date = new DateTime('2024-01-01');
        $result = $this->game->setPublishedAt($date);
        $this->assertSame($date, $this->game->getPublishedAt());
        $this->assertSame($this->game, $result);
    }

    public function testSetPublishedAtToNull(): void
    {
        $this->game->setPublishedAt(new DateTime());
        $this->game->setPublishedAt(null);
        $this->assertNull($this->game->getPublishedAt());
    }

    // ------------------------------------------------------------------
    // Trait properties
    // ------------------------------------------------------------------

    public function testNbChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->game->getNbChart());
    }

    public function testSetAndGetNbChart(): void
    {
        $this->game->setNbChart(10);
        $this->assertSame(10, $this->game->getNbChart());
    }

    public function testNbPostDefaultsToZero(): void
    {
        $this->assertSame(0, $this->game->getNbPost());
    }

    public function testNbPlayerDefaultsToZero(): void
    {
        $this->assertSame(0, $this->game->getNbPlayer());
    }

    public function testNbTeamDefaultsToZero(): void
    {
        $this->assertSame(0, $this->game->getNbTeam());
    }

    // ------------------------------------------------------------------
    // IgdbGame relation
    // ------------------------------------------------------------------

    public function testIgdbGameDefaultsToNull(): void
    {
        $this->assertNull($this->game->getIgdbGame());
    }

    public function testSetAndGetIgdbGame(): void
    {
        $igdbGame = $this->createMock(IgdbGame::class);
        $result = $this->game->setIgdbGame($igdbGame);
        $this->assertSame($igdbGame, $this->game->getIgdbGame());
        $this->assertSame($this->game, $result);
    }

    public function testSetIgdbGameToNull(): void
    {
        $igdbGame = $this->createMock(IgdbGame::class);
        $this->game->setIgdbGame($igdbGame);
        $this->game->setIgdbGame(null);
        $this->assertNull($this->game->getIgdbGame());
    }

    public function testGetIgdbIdReturnsNullWhenNoIgdbGame(): void
    {
        $this->assertNull($this->game->getIgdbId());
    }

    public function testGetIgdbIdDelegatesToIgdbGame(): void
    {
        $igdbGame = $this->createMock(IgdbGame::class);
        $igdbGame->method('getId')->willReturn(999);
        $this->game->setIgdbGame($igdbGame);

        $this->assertSame(999, $this->game->getIgdbId());
    }

    // ------------------------------------------------------------------
    // Serie relation
    // ------------------------------------------------------------------

    public function testSerieDefaultsToNull(): void
    {
        $this->assertNull($this->game->getSerie());
    }

    public function testSetAndGetSerie(): void
    {
        $serie = $this->createMock(Serie::class);
        $result = $this->game->setSerie($serie);
        $this->assertSame($serie, $this->game->getSerie());
        $this->assertSame($this->game, $result);
    }

    public function testSetSerieToNull(): void
    {
        $serie = $this->createMock(Serie::class);
        $this->game->setSerie($serie);
        $this->game->setSerie(null);
        $this->assertNull($this->game->getSerie());
    }

    // ------------------------------------------------------------------
    // Badge relation
    // ------------------------------------------------------------------

    public function testSetAndGetBadge(): void
    {
        $badge = $this->createMock(MasterBadge::class);
        $result = $this->game->setBadge($badge);
        $this->assertSame($badge, $this->game->getBadge());
        $this->assertSame($this->game, $result);
    }

    // ------------------------------------------------------------------
    // Forum relation
    // ------------------------------------------------------------------

    public function testSetAndGetForum(): void
    {
        $forum = $this->createMock(Forum::class);
        $result = $this->game->setForum($forum);
        $this->assertSame($forum, $this->game->getForum());
        $this->assertSame($this->game, $result);
    }

    // ------------------------------------------------------------------
    // LastScore relation
    // ------------------------------------------------------------------

    public function testLastScoreDefaultsToNull(): void
    {
        $this->assertNull($this->game->getLastScore());
    }

    public function testSetAndGetLastScore(): void
    {
        $pc = $this->createMock(PlayerChart::class);
        $result = $this->game->setLastScore($pc);
        $this->assertSame($pc, $this->game->getLastScore());
        $this->assertSame($this->game, $result);
    }

    public function testSetLastScoreToNull(): void
    {
        $pc = $this->createMock(PlayerChart::class);
        $this->game->setLastScore($pc);
        $this->game->setLastScore(null);
        $this->assertNull($this->game->getLastScore());
    }

    // ------------------------------------------------------------------
    // Groups collection
    // ------------------------------------------------------------------

    public function testAddGroup(): void
    {
        $group = $this->createMock(Group::class);
        $group->expects($this->once())->method('setGame')->with($this->game);

        $this->game->addGroup($group);

        $this->assertCount(1, $this->game->getGroups());
    }

    public function testRemoveGroup(): void
    {
        $group = new Group();
        $this->game->addGroup($group);
        $this->game->removeGroup($group);

        $this->assertCount(0, $this->game->getGroups());
    }

    // ------------------------------------------------------------------
    // Platforms collection
    // ------------------------------------------------------------------

    public function testAddPlatform(): void
    {
        $platform = $this->createMock(Platform::class);
        $this->game->addPlatform($platform);

        $this->assertCount(1, $this->game->getPlatforms());
        $this->assertTrue($this->game->getPlatforms()->contains($platform));
    }

    public function testRemovePlatform(): void
    {
        $platform = $this->createMock(Platform::class);
        $this->game->addPlatform($platform);
        $this->game->removePlatform($platform);

        $this->assertCount(0, $this->game->getPlatforms());
    }

    // ------------------------------------------------------------------
    // Rules collection
    // ------------------------------------------------------------------

    public function testAddRule(): void
    {
        $rule = $this->createMock(Rule::class);
        $this->game->addRule($rule);

        $this->assertCount(1, $this->game->getRules());
    }

    public function testRemoveRule(): void
    {
        $rule = $this->createMock(Rule::class);
        $this->game->addRule($rule);
        $this->game->removeRule($rule);

        $this->assertCount(0, $this->game->getRules());
    }

    // ------------------------------------------------------------------
    // Discords collection
    // ------------------------------------------------------------------

    public function testAddDiscordDoesNotDuplicate(): void
    {
        $discord = $this->createMock(Discord::class);
        $discord->method('addGame')->willReturnSelf();

        $this->game->addDiscord($discord);
        $this->game->addDiscord($discord);

        $this->assertCount(1, $this->game->getDiscords());
    }

    public function testRemoveDiscord(): void
    {
        $discord = $this->createMock(Discord::class);
        $discord->method('addGame')->willReturnSelf();
        $discord->method('removeGame')->willReturnSelf();

        $this->game->addDiscord($discord);
        $this->game->removeDiscord($discord);

        $this->assertCount(0, $this->game->getDiscords());
    }

    // ------------------------------------------------------------------
    // IGDB-delegated methods
    // ------------------------------------------------------------------

    public function testGetGenresReturnsEmptyCollectionWhenNoIgdbGame(): void
    {
        $genres = $this->game->getGenres();
        $this->assertInstanceOf(Collection::class, $genres);
        $this->assertCount(0, $genres);
    }

    public function testGetSummaryReturnsNullWhenNoIgdbGame(): void
    {
        $this->assertNull($this->game->getSummary());
    }

    public function testGetStorylineReturnsNullWhenNoIgdbGame(): void
    {
        $this->assertNull($this->game->getStoryline());
    }

    public function testGetReleaseDateReturnsNullWhenNoIgdbGame(): void
    {
        $this->assertNull($this->game->getReleaseDate());
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testGetDefaultName(): void
    {
        $this->game->setLibGameEn('Zelda');
        $this->assertSame('Zelda', $this->game->getDefaultName());
    }

    public function testGetNameEnglish(): void
    {
        $this->game->setLibGameEn('Zelda EN');
        $this->game->setLibGameFr('Zelda FR');
        $this->assertSame('Zelda EN', $this->game->getName('en'));
    }

    public function testGetNameFrench(): void
    {
        $this->game->setLibGameEn('Zelda EN');
        $this->game->setLibGameFr('Zelda FR');
        $this->assertSame('Zelda FR', $this->game->getName('fr'));
    }

    public function testToStringWithNameAndId(): void
    {
        $this->game->setId(10);
        $this->game->setLibGameEn('Zelda');
        $this->assertSame('Zelda (10)', (string) $this->game);
    }
}
