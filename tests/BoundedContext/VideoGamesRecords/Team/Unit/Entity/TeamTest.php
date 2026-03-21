<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Team\Unit\Entity;

use App\BoundedContext\Forum\Domain\Entity\Forum;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\TeamBadge;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamGame;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class TeamTest extends TestCase
{
    private Team $team;

    protected function setUp(): void
    {
        $this->team = new Team();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesEmptyCollections(): void
    {
        $team = new Team();

        $this->assertInstanceOf(Collection::class, $team->getPlayers());
        $this->assertCount(0, $team->getPlayers());
        $this->assertInstanceOf(Collection::class, $team->getTeamGame());
        $this->assertCount(0, $team->getTeamGame());
        $this->assertInstanceOf(Collection::class, $team->getTeamBadge());
        $this->assertCount(0, $team->getTeamBadge());
    }

    // ------------------------------------------------------------------
    // Basic properties getters / setters
    // ------------------------------------------------------------------

    public function testSetAndGetId(): void
    {
        $this->team->setId(1);
        $this->assertSame(1, $this->team->getId());
    }

    public function testSetIdReturnsStatic(): void
    {
        $result = $this->team->setId(5);
        $this->assertSame($this->team, $result);
    }

    public function testSetAndGetLibTeam(): void
    {
        $this->team->setLibTeam('Alpha Team');
        $this->assertSame('Alpha Team', $this->team->getLibTeam());
    }

    public function testSetLibTeamReturnsStatic(): void
    {
        $result = $this->team->setLibTeam('Alpha Team');
        $this->assertSame($this->team, $result);
    }

    public function testGetNameReturnsLibTeam(): void
    {
        $this->team->setLibTeam('Beta Squad');
        $this->assertSame('Beta Squad', $this->team->getName());
    }

    public function testSetAndGetTag(): void
    {
        $this->team->setTag('ALF');
        $this->assertSame('ALF', $this->team->getTag());
    }

    public function testSetTagReturnsStatic(): void
    {
        $result = $this->team->setTag('ALF');
        $this->assertSame($this->team, $result);
    }

    public function testSiteWebDefaultsToNull(): void
    {
        $this->assertNull($this->team->getSiteWeb());
    }

    public function testSetAndGetSiteWeb(): void
    {
        $this->team->setSiteWeb('https://example.com');
        $this->assertSame('https://example.com', $this->team->getSiteWeb());
    }

    public function testSetSiteWebToNull(): void
    {
        $this->team->setSiteWeb('https://example.com');
        $this->team->setSiteWeb(null);
        $this->assertNull($this->team->getSiteWeb());
    }

    public function testSetSiteWebReturnsStatic(): void
    {
        $result = $this->team->setSiteWeb(null);
        $this->assertSame($this->team, $result);
    }

    public function testLogoDefaultValue(): void
    {
        $this->assertSame('default.png', $this->team->getLogo());
    }

    public function testSetAndGetLogo(): void
    {
        $this->team->setLogo('team_logo.png');
        $this->assertSame('team_logo.png', $this->team->getLogo());
    }

    public function testSetLogoReturnsStatic(): void
    {
        $result = $this->team->setLogo('logo.png');
        $this->assertSame($this->team, $result);
    }

    public function testPresentationDefaultsToNull(): void
    {
        $this->assertNull($this->team->getPresentation());
    }

    public function testSetAndGetPresentation(): void
    {
        $this->team->setPresentation('We are the best team.');
        $this->assertSame('We are the best team.', $this->team->getPresentation());
    }

    public function testSetPresentationReturnsStatic(): void
    {
        $result = $this->team->setPresentation('desc');
        $this->assertSame($this->team, $result);
    }

    public function testStatusDefaultsToClosed(): void
    {
        $this->assertSame(Team::STATUS_CLOSED, $this->team->getStatus());
    }

    public function testSetAndGetStatus(): void
    {
        $this->team->setStatus(Team::STATUS_OPENED);
        $this->assertSame(Team::STATUS_OPENED, $this->team->getStatus());
    }

    public function testSetStatusReturnsStatic(): void
    {
        $result = $this->team->setStatus(Team::STATUS_OPENED);
        $this->assertSame($this->team, $result);
    }

    // ------------------------------------------------------------------
    // isOpened
    // ------------------------------------------------------------------

    public function testIsOpenedReturnsFalseWhenClosed(): void
    {
        $this->team->setStatus(Team::STATUS_CLOSED);
        $this->assertFalse($this->team->isOpened());
    }

    public function testIsOpenedReturnsTrueWhenOpened(): void
    {
        $this->team->setStatus(Team::STATUS_OPENED);
        $this->assertTrue($this->team->isOpened());
    }

    // ------------------------------------------------------------------
    // Leader relation
    // ------------------------------------------------------------------

    public function testSetAndGetLeader(): void
    {
        $player = $this->createMock(Player::class);
        $this->team->setLeader($player);
        $this->assertSame($player, $this->team->getLeader());
    }

    public function testSetLeaderReturnsStatic(): void
    {
        $player = $this->createMock(Player::class);
        $result = $this->team->setLeader($player);
        $this->assertSame($this->team, $result);
    }

    // ------------------------------------------------------------------
    // Forum relation
    // ------------------------------------------------------------------

    public function testForumDefaultsToNull(): void
    {
        $this->assertNull($this->team->getForum());
    }

    public function testSetAndGetForum(): void
    {
        $forum = $this->createMock(Forum::class);
        $this->team->setForum($forum);
        $this->assertSame($forum, $this->team->getForum());
    }

    public function testSetForumToNull(): void
    {
        $forum = $this->createMock(Forum::class);
        $this->team->setForum($forum);
        $this->team->setForum(null);
        $this->assertNull($this->team->getForum());
    }

    public function testSetForumReturnsStatic(): void
    {
        $result = $this->team->setForum(null);
        $this->assertSame($this->team, $result);
    }

    // ------------------------------------------------------------------
    // Trait properties (default values)
    // ------------------------------------------------------------------

    public function testRankCupDefaultsToZero(): void
    {
        $this->assertSame(0, $this->team->getRankCup());
    }

    public function testSetAndGetRankCup(): void
    {
        $this->team->setRankCup(3);
        $this->assertSame(3, $this->team->getRankCup());
    }

    public function testPointChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->team->getPointChart());
    }

    public function testSetAndGetPointChart(): void
    {
        $this->team->setPointChart(500);
        $this->assertSame(500, $this->team->getPointChart());
    }

    public function testPointGameDefaultsToZero(): void
    {
        $this->assertSame(0, $this->team->getPointGame());
    }

    public function testSetAndGetPointGame(): void
    {
        $this->team->setPointGame(200);
        $this->assertSame(200, $this->team->getPointGame());
    }

    public function testPointBadgeDefaultsToZero(): void
    {
        $this->assertSame(0, $this->team->getPointBadge());
    }

    public function testSetAndGetPointBadge(): void
    {
        $this->team->setPointBadge(50);
        $this->assertSame(50, $this->team->getPointBadge());
    }

    public function testRankPointChartDefaultsToZero(): void
    {
        $this->assertSame(0, $this->team->getRankPointChart());
    }

    public function testSetAndGetRankPointChart(): void
    {
        $this->team->setRankPointChart(10);
        $this->assertSame(10, $this->team->getRankPointChart());
    }

    public function testRankPointGameDefaultsToZero(): void
    {
        $this->assertSame(0, $this->team->getRankPointGame());
    }

    public function testSetAndGetRankPointGame(): void
    {
        $this->team->setRankPointGame(7);
        $this->assertSame(7, $this->team->getRankPointGame());
    }

    public function testRankBadgeDefaultsToZero(): void
    {
        $this->assertSame(0, $this->team->getRankBadge());
    }

    public function testSetAndGetRankBadge(): void
    {
        $this->team->setRankBadge(4);
        $this->assertSame(4, $this->team->getRankBadge());
    }

    public function testChartRank0DefaultsToZero(): void
    {
        $this->assertSame(0, $this->team->getChartRank0());
    }

    public function testSetAndGetChartRank0(): void
    {
        $this->team->setChartRank0(15);
        $this->assertSame(15, $this->team->getChartRank0());
    }

    public function testChartRank1DefaultsToZero(): void
    {
        $this->assertSame(0, $this->team->getChartRank1());
    }

    public function testSetAndGetChartRank1(): void
    {
        $this->team->setChartRank1(12);
        $this->assertSame(12, $this->team->getChartRank1());
    }

    public function testChartRank2DefaultsToZero(): void
    {
        $this->assertSame(0, $this->team->getChartRank2());
    }

    public function testChartRank3DefaultsToZero(): void
    {
        $this->assertSame(0, $this->team->getChartRank3());
    }

    public function testGameRank0DefaultsToZero(): void
    {
        $this->assertSame(0, $this->team->getGameRank0());
    }

    public function testSetAndGetGameRank0(): void
    {
        $this->team->setGameRank0(8);
        $this->assertSame(8, $this->team->getGameRank0());
    }

    public function testGameRank1DefaultsToZero(): void
    {
        $this->assertSame(0, $this->team->getGameRank1());
    }

    public function testGameRank2DefaultsToZero(): void
    {
        $this->assertSame(0, $this->team->getGameRank2());
    }

    public function testGameRank3DefaultsToZero(): void
    {
        $this->assertSame(0, $this->team->getGameRank3());
    }

    public function testNbPlayerDefaultsToZero(): void
    {
        $this->assertSame(0, $this->team->getNbPlayer());
    }

    public function testSetAndGetNbPlayer(): void
    {
        $this->team->setNbPlayer(5);
        $this->assertSame(5, $this->team->getNbPlayer());
    }

    public function testNbGameDefaultsToZero(): void
    {
        $this->assertSame(0, $this->team->getNbGame());
    }

    public function testSetAndGetNbGame(): void
    {
        $this->team->setNbGame(20);
        $this->assertSame(20, $this->team->getNbGame());
    }

    public function testNbMasterBadgeDefaultsToZero(): void
    {
        $this->assertSame(0, $this->team->getNbMasterBadge());
    }

    public function testSetAndGetNbMasterBadge(): void
    {
        $this->team->setNbMasterBadge(3);
        $this->assertSame(3, $this->team->getNbMasterBadge());
    }

    public function testAverageChartRankDefaultsToNull(): void
    {
        $this->assertNull($this->team->getAverageChartRank());
    }

    public function testSetAndGetAverageChartRank(): void
    {
        $this->team->setAverageChartRank(2.5);
        $this->assertSame(2.5, $this->team->getAverageChartRank());
    }

    public function testAverageGameRankDefaultsToNull(): void
    {
        $this->assertNull($this->team->getAverageGameRank());
    }

    public function testSetAndGetAverageGameRank(): void
    {
        $this->team->setAverageGameRank(3.7);
        $this->assertSame(3.7, $this->team->getAverageGameRank());
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testToStringReturnsId(): void
    {
        $this->team->setId(42);
        $this->assertSame('42', (string) $this->team);
    }

    public function testGetSluggableFields(): void
    {
        $this->assertSame(['libTeam'], $this->team->getSluggableFields());
    }

    public function testGetStatusChoices(): void
    {
        $choices = Team::getStatusChoices();
        $this->assertArrayHasKey(Team::STATUS_CLOSED, $choices);
        $this->assertArrayHasKey(Team::STATUS_OPENED, $choices);
        $this->assertSame(Team::STATUS_CLOSED, $choices[Team::STATUS_CLOSED]);
        $this->assertSame(Team::STATUS_OPENED, $choices[Team::STATUS_OPENED]);
    }

    // ------------------------------------------------------------------
    // Status constants
    // ------------------------------------------------------------------

    public function testStatusOpenedConstant(): void
    {
        $this->assertSame('OPENED', Team::STATUS_OPENED);
    }

    public function testStatusClosedConstant(): void
    {
        $this->assertSame('CLOSED', Team::STATUS_CLOSED);
    }
}
