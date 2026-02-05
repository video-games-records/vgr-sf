<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerStatusEnum;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use DateTime;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PlayerTest extends TestCase
{
    private Player $player;

    protected function setUp(): void
    {
        $this->player = new Player();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesEmptyCollections(): void
    {
        $player = new Player();

        $this->assertInstanceOf(Collection::class, $player->getPlayerCharts());
        $this->assertCount(0, $player->getPlayerCharts());
        $this->assertInstanceOf(Collection::class, $player->getFriends());
        $this->assertCount(0, $player->getFriends());
    }

    // ------------------------------------------------------------------
    // Basic properties getters / setters
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->player->getId());
    }

    public function testSetAndGetId(): void
    {
        $this->player->setId(42);
        $this->assertSame(42, $this->player->getId());
    }

    public function testSetAndGetPseudo(): void
    {
        $this->player->setPseudo('GamerX');
        $this->assertSame('GamerX', $this->player->getPseudo());
    }

    public function testAvatarDefaultValue(): void
    {
        $this->assertSame('default.jpg', $this->player->getAvatar());
    }

    public function testSetAndGetAvatar(): void
    {
        $this->player->setAvatar('custom.png');
        $this->assertSame('custom.png', $this->player->getAvatar());
    }

    public function testGamerCardDefaultsToNull(): void
    {
        $this->assertNull($this->player->getGamerCard());
    }

    public function testSetAndGetGamerCard(): void
    {
        $this->player->setGamerCard('MyCard');
        $this->assertSame('MyCard', $this->player->getGamerCard());
    }

    public function testSetGamerCardToNull(): void
    {
        $this->player->setGamerCard('MyCard');
        $this->player->setGamerCard(null);
        $this->assertNull($this->player->getGamerCard());
    }

    public function testRankProofDefaultsToZero(): void
    {
        $this->assertSame(0, $this->player->getRankProof());
    }

    public function testSetAndGetRankProof(): void
    {
        $this->player->setRankProof(5);
        $this->assertSame(5, $this->player->getRankProof());
    }

    public function testRankCountryDefaultsToZero(): void
    {
        $this->assertSame(0, $this->player->getRankCountry());
    }

    public function testSetAndGetRankCountry(): void
    {
        $this->player->setRankCountry(12);
        $this->assertSame(12, $this->player->getRankCountry());
    }

    public function testNbChartMaxDefaultsToZero(): void
    {
        $this->assertSame(0, $this->player->getNbChartMax());
    }

    public function testSetAndGetNbChartMax(): void
    {
        $this->player->setNbChartMax(100);
        $this->assertSame(100, $this->player->getNbChartMax());
    }

    public function testNbChartWithPlatformDefaultsToZero(): void
    {
        $this->assertSame(0, $this->player->getNbChartWithPlatform());
    }

    public function testSetAndGetNbChartWithPlatform(): void
    {
        $this->player->setNbChartWithPlatform(50);
        $this->assertSame(50, $this->player->getNbChartWithPlatform());
    }

    public function testNbChartDisabledDefaultsToZero(): void
    {
        $this->assertSame(0, $this->player->getNbChartDisabled());
    }

    public function testSetAndGetNbChartDisabled(): void
    {
        $this->player->setNbChartDisabled(3);
        $this->assertSame(3, $this->player->getNbChartDisabled());
    }

    public function testLastLoginDefaultsToNull(): void
    {
        $this->assertNull($this->player->getLastLogin());
    }

    public function testSetAndGetLastLogin(): void
    {
        $date = new DateTime('2025-01-15 10:30:00');
        $this->player->setLastLogin($date);
        $this->assertSame($date, $this->player->getLastLogin());
    }

    public function testSetLastLoginToNull(): void
    {
        $this->player->setLastLogin(new DateTime());
        $this->player->setLastLogin(null);
        $this->assertNull($this->player->getLastLogin());
    }

    public function testNbConnexionDefaultsToZero(): void
    {
        $this->assertSame(0, $this->player->getNbConnexion());
    }

    public function testSetAndGetNbConnexion(): void
    {
        $this->player->setNbConnexion(150);
        $this->assertSame(150, $this->player->getNbConnexion());
    }

    public function testBoolMajDefaultsToFalse(): void
    {
        $this->assertFalse($this->player->getBoolMaj());
    }

    public function testSetAndGetBoolMaj(): void
    {
        $this->player->setBoolMaj(true);
        $this->assertTrue($this->player->getBoolMaj());
    }

    public function testHasDonateDefaultsToFalse(): void
    {
        $this->assertFalse($this->player->getHasDonate());
    }

    public function testSetAndGetHasDonate(): void
    {
        $this->player->setHasDonate(true);
        $this->assertTrue($this->player->getHasDonate());
    }

    public function testSetAndGetLastDisplayLostPosition(): void
    {
        $date = new DateTime('2025-06-01');
        $this->player->setLastDisplayLostPosition($date);
        $this->assertSame($date, $this->player->getLastDisplayLostPosition());
    }

    public function testSetLastDisplayLostPositionToNull(): void
    {
        $this->player->setLastDisplayLostPosition(new DateTime());
        $this->player->setLastDisplayLostPosition(null);
        $this->assertNull($this->player->getLastDisplayLostPosition());
    }

    // ------------------------------------------------------------------
    // UserId
    // ------------------------------------------------------------------

    public function testSetAndGetUserId(): void
    {
        $result = $this->player->setUserId(7);
        $this->assertSame(7, $this->player->getUserId());
        $this->assertSame($this->player, $result);
    }

    // ------------------------------------------------------------------
    // Team relation
    // ------------------------------------------------------------------

    public function testTeamDefaultsToNull(): void
    {
        $this->assertNull($this->player->getTeam());
    }

    public function testSetAndGetTeam(): void
    {
        $team = $this->createMock(Team::class);
        $this->player->setTeam($team);
        $this->assertSame($team, $this->player->getTeam());
    }

    public function testSetTeamToNull(): void
    {
        $team = $this->createMock(Team::class);
        $this->player->setTeam($team);
        $this->player->setTeam(null);
        $this->assertNull($this->player->getTeam());
    }

    // ------------------------------------------------------------------
    // Status & delegation to PlayerStatusEnum
    // ------------------------------------------------------------------

    public function testSetAndGetStatus(): void
    {
        $this->player->setStatus(PlayerStatusEnum::MEMBER);
        $this->assertSame(PlayerStatusEnum::MEMBER, $this->player->getStatus());
    }

    public function testGetStatusLabel(): void
    {
        $this->player->setStatus(PlayerStatusEnum::ADMINISTRATOR);
        $this->assertSame('Administrator', $this->player->getStatusLabel());
    }

    public function testGetStatusFrenchLabel(): void
    {
        $this->player->setStatus(PlayerStatusEnum::MODERATOR);
        $this->assertSame('Modérateur', $this->player->getStatusFrenchLabel());
    }

    public function testGetStatusClass(): void
    {
        $this->player->setStatus(PlayerStatusEnum::CHIEF_STAFF);
        $this->assertSame('chief_staff', $this->player->getStatusClass());
    }

    // ------------------------------------------------------------------
    // Permission checks (isAdmin, isModerator, canManageProofs, canManageGames)
    // ------------------------------------------------------------------

    #[DataProvider('adminStatusProvider')]
    public function testIsAdminReturnsTrueForAdminStatuses(PlayerStatusEnum $status): void
    {
        $this->player->setStatus($status);
        $this->assertTrue($this->player->isAdmin());
    }

    public static function adminStatusProvider(): array
    {
        return [
            'WEBMASTER' => [PlayerStatusEnum::WEBMASTER],
            'ADMINISTRATOR' => [PlayerStatusEnum::ADMINISTRATOR],
            'PROOF_ADMIN' => [PlayerStatusEnum::PROOF_ADMIN],
            'GAME_AND_PROOF_ADMIN' => [PlayerStatusEnum::GAME_AND_PROOF_ADMIN],
            'CHIEF_PROOF_ADMIN' => [PlayerStatusEnum::CHIEF_PROOF_ADMIN],
            'CHIEF_STAFF' => [PlayerStatusEnum::CHIEF_STAFF],
        ];
    }

    #[DataProvider('nonAdminStatusProvider')]
    public function testIsAdminReturnsFalseForNonAdminStatuses(PlayerStatusEnum $status): void
    {
        $this->player->setStatus($status);
        $this->assertFalse($this->player->isAdmin());
    }

    public static function nonAdminStatusProvider(): array
    {
        return [
            'MEMBER' => [PlayerStatusEnum::MEMBER],
            'DEVELOPER' => [PlayerStatusEnum::DEVELOPER],
            'DESIGNER' => [PlayerStatusEnum::DESIGNER],
            'GAME_ADDER' => [PlayerStatusEnum::GAME_ADDER],
            'TRANSLATOR' => [PlayerStatusEnum::TRANSLATOR],
            'MODERATOR' => [PlayerStatusEnum::MODERATOR],
            'REDACTOR' => [PlayerStatusEnum::REDACTOR],
            'REFEREE' => [PlayerStatusEnum::REFEREE],
            'STREAMER' => [PlayerStatusEnum::STREAMER],
        ];
    }

    #[DataProvider('moderatorStatusProvider')]
    public function testIsModeratorReturnsTrueForModeratorStatuses(PlayerStatusEnum $status): void
    {
        $this->player->setStatus($status);
        $this->assertTrue($this->player->isModerator());
    }

    public static function moderatorStatusProvider(): array
    {
        return [
            'MODERATOR' => [PlayerStatusEnum::MODERATOR],
            'ADMINISTRATOR' => [PlayerStatusEnum::ADMINISTRATOR],
            'CHIEF_STAFF' => [PlayerStatusEnum::CHIEF_STAFF],
        ];
    }

    #[DataProvider('nonModeratorStatusProvider')]
    public function testIsModeratorReturnsFalseForNonModeratorStatuses(PlayerStatusEnum $status): void
    {
        $this->player->setStatus($status);
        $this->assertFalse($this->player->isModerator());
    }

    public static function nonModeratorStatusProvider(): array
    {
        return [
            'MEMBER' => [PlayerStatusEnum::MEMBER],
            'WEBMASTER' => [PlayerStatusEnum::WEBMASTER],
            'GAME_ADDER' => [PlayerStatusEnum::GAME_ADDER],
            'PROOF_ADMIN' => [PlayerStatusEnum::PROOF_ADMIN],
            'STREAMER' => [PlayerStatusEnum::STREAMER],
        ];
    }

    #[DataProvider('proofManagerStatusProvider')]
    public function testCanManageProofsReturnsTrueForProofManagers(PlayerStatusEnum $status): void
    {
        $this->player->setStatus($status);
        $this->assertTrue($this->player->canManageProofs());
    }

    public static function proofManagerStatusProvider(): array
    {
        return [
            'PROOF_ADMIN' => [PlayerStatusEnum::PROOF_ADMIN],
            'REFEREE' => [PlayerStatusEnum::REFEREE],
            'GAME_AND_PROOF_ADMIN' => [PlayerStatusEnum::GAME_AND_PROOF_ADMIN],
            'CHIEF_PROOF_ADMIN' => [PlayerStatusEnum::CHIEF_PROOF_ADMIN],
            'CHIEF_STAFF' => [PlayerStatusEnum::CHIEF_STAFF],
        ];
    }

    #[DataProvider('nonProofManagerStatusProvider')]
    public function testCanManageProofsReturnsFalseForNonProofManagers(PlayerStatusEnum $status): void
    {
        $this->player->setStatus($status);
        $this->assertFalse($this->player->canManageProofs());
    }

    public static function nonProofManagerStatusProvider(): array
    {
        return [
            'MEMBER' => [PlayerStatusEnum::MEMBER],
            'MODERATOR' => [PlayerStatusEnum::MODERATOR],
            'ADMINISTRATOR' => [PlayerStatusEnum::ADMINISTRATOR],
            'STREAMER' => [PlayerStatusEnum::STREAMER],
        ];
    }

    #[DataProvider('gameManagerStatusProvider')]
    public function testCanManageGamesReturnsTrueForGameManagers(PlayerStatusEnum $status): void
    {
        $this->player->setStatus($status);
        $this->assertTrue($this->player->canManageGames());
    }

    public static function gameManagerStatusProvider(): array
    {
        return [
            'GAME_ADDER' => [PlayerStatusEnum::GAME_ADDER],
            'GAME_AND_PROOF_ADMIN' => [PlayerStatusEnum::GAME_AND_PROOF_ADMIN],
            'ADMINISTRATOR' => [PlayerStatusEnum::ADMINISTRATOR],
            'CHIEF_STAFF' => [PlayerStatusEnum::CHIEF_STAFF],
        ];
    }

    #[DataProvider('nonGameManagerStatusProvider')]
    public function testCanManageGamesReturnsFalseForNonGameManagers(PlayerStatusEnum $status): void
    {
        $this->player->setStatus($status);
        $this->assertFalse($this->player->canManageGames());
    }

    public static function nonGameManagerStatusProvider(): array
    {
        return [
            'MEMBER' => [PlayerStatusEnum::MEMBER],
            'MODERATOR' => [PlayerStatusEnum::MODERATOR],
            'PROOF_ADMIN' => [PlayerStatusEnum::PROOF_ADMIN],
            'STREAMER' => [PlayerStatusEnum::STREAMER],
        ];
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testToString(): void
    {
        $this->player->setId(10);
        $this->player->setPseudo('Alice');
        $this->assertSame('Alice (10)', (string) $this->player);
    }

    public function testGetSluggableFields(): void
    {
        $this->assertSame(['pseudo'], $this->player->getSluggableFields());
    }

    public function testGetInitial(): void
    {
        $this->player->setPseudo('Zelda');
        $this->assertSame('Z', $this->player->getInitial());
    }

    public function testGetInitialWithLowerCase(): void
    {
        $this->player->setPseudo('mario');
        $this->assertSame('m', $this->player->getInitial());
    }

    // ------------------------------------------------------------------
    // Friends collection
    // ------------------------------------------------------------------

    public function testAddFriend(): void
    {
        $friend = new Player();
        $friend->setPseudo('FriendA');

        $result = $this->player->addFriend($friend);

        $this->assertCount(1, $this->player->getFriends());
        $this->assertTrue($this->player->getFriends()->contains($friend));
        $this->assertSame($this->player, $result);
    }

    public function testAddFriendDoesNotDuplicate(): void
    {
        $friend = new Player();
        $friend->setPseudo('FriendA');

        $this->player->addFriend($friend);
        $this->player->addFriend($friend);

        $this->assertCount(1, $this->player->getFriends());
    }

    public function testRemoveFriend(): void
    {
        $friend = new Player();
        $friend->setPseudo('FriendA');

        $this->player->addFriend($friend);
        $result = $this->player->removeFriend($friend);

        $this->assertCount(0, $this->player->getFriends());
        $this->assertFalse($this->player->getFriends()->contains($friend));
        $this->assertSame($this->player, $result);
    }

    public function testRemoveNonExistentFriendDoesNothing(): void
    {
        $friend = new Player();
        $friend->setPseudo('Ghost');

        $this->player->removeFriend($friend);

        $this->assertCount(0, $this->player->getFriends());
    }

    // ------------------------------------------------------------------
    // isLeader
    // ------------------------------------------------------------------

    public function testIsLeaderReturnsTrueWhenPlayerIsTeamLeader(): void
    {
        $this->player->setId(1);
        $this->player->setPseudo('Leader');

        $team = $this->createMock(Team::class);
        $team->method('getLeader')->willReturn($this->player);

        $this->player->setTeam($team);

        $this->assertTrue($this->player->isLeader());
    }

    public function testIsLeaderReturnsFalseWhenPlayerIsNotTeamLeader(): void
    {
        $this->player->setId(1);
        $this->player->setPseudo('NotLeader');

        $leader = new Player();
        $leader->setId(2);
        $leader->setPseudo('ActualLeader');

        $team = $this->createMock(Team::class);
        $team->method('getLeader')->willReturn($leader);

        $this->player->setTeam($team);

        $this->assertFalse($this->player->isLeader());
    }

    public function testIsLeaderReturnsFalseWhenNoTeam(): void
    {
        $this->assertFalse($this->player->isLeader());
    }
}
