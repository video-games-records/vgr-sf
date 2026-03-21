<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Team\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\TeamRequest;
use App\BoundedContext\VideoGamesRecords\Team\Domain\ValueObject\TeamRequestStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TeamRequestTest extends TestCase
{
    private TeamRequest $teamRequest;

    protected function setUp(): void
    {
        $this->teamRequest = new TeamRequest();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->teamRequest->getId());
    }

    public function testStatusDefaultsToActive(): void
    {
        $this->assertSame(TeamRequestStatus::ACTIVE, $this->teamRequest->getStatus());
    }

    // ------------------------------------------------------------------
    // Basic properties getters / setters
    // ------------------------------------------------------------------

    public function testSetAndGetId(): void
    {
        $this->teamRequest->setId(10);
        $this->assertSame(10, $this->teamRequest->getId());
    }

    public function testSetIdReturnsStatic(): void
    {
        $result = $this->teamRequest->setId(1);
        $this->assertSame($this->teamRequest, $result);
    }

    // ------------------------------------------------------------------
    // Status
    // ------------------------------------------------------------------

    #[DataProvider('validStatusProvider')]
    public function testSetStatusAcceptsValidValues(string $status): void
    {
        $this->teamRequest->setStatus($status);
        $this->assertSame($status, $this->teamRequest->getStatus());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validStatusProvider(): array
    {
        return [
            TeamRequestStatus::ACTIVE   => [TeamRequestStatus::ACTIVE],
            TeamRequestStatus::ACCEPTED => [TeamRequestStatus::ACCEPTED],
            TeamRequestStatus::REFUSED  => [TeamRequestStatus::REFUSED],
            TeamRequestStatus::CANCELED => [TeamRequestStatus::CANCELED],
        ];
    }

    public function testSetStatusReturnsStatic(): void
    {
        $result = $this->teamRequest->setStatus(TeamRequestStatus::ACTIVE);
        $this->assertSame($this->teamRequest, $result);
    }

    public function testSetStatusRejectsInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->teamRequest->setStatus('INVALID_STATUS');
    }

    // ------------------------------------------------------------------
    // getTeamRequestStatus value object
    // ------------------------------------------------------------------

    public function testGetTeamRequestStatusReturnsValueObject(): void
    {
        $this->teamRequest->setStatus(TeamRequestStatus::ACCEPTED);
        $vo = $this->teamRequest->getTeamRequestStatus();

        $this->assertInstanceOf(TeamRequestStatus::class, $vo);
        $this->assertSame(TeamRequestStatus::ACCEPTED, $vo->getValue());
    }

    public function testGetTeamRequestStatusIsActiveByDefault(): void
    {
        $vo = $this->teamRequest->getTeamRequestStatus();
        $this->assertTrue($vo->isActive());
    }

    public function testGetTeamRequestStatusIsAccepted(): void
    {
        $this->teamRequest->setStatus(TeamRequestStatus::ACCEPTED);
        $this->assertTrue($this->teamRequest->getTeamRequestStatus()->isAccepted());
    }

    public function testGetTeamRequestStatusIsRefused(): void
    {
        $this->teamRequest->setStatus(TeamRequestStatus::REFUSED);
        $this->assertTrue($this->teamRequest->getTeamRequestStatus()->isRefused());
    }

    public function testGetTeamRequestStatusIsCanceled(): void
    {
        $this->teamRequest->setStatus(TeamRequestStatus::CANCELED);
        $this->assertTrue($this->teamRequest->getTeamRequestStatus()->isCanceled());
    }

    // ------------------------------------------------------------------
    // Player relation
    // ------------------------------------------------------------------

    public function testSetAndGetPlayer(): void
    {
        $player = $this->createMock(Player::class);
        $this->teamRequest->setPlayer($player);
        $this->assertSame($player, $this->teamRequest->getPlayer());
    }

    public function testSetPlayerReturnsStatic(): void
    {
        $player = $this->createMock(Player::class);
        $result = $this->teamRequest->setPlayer($player);
        $this->assertSame($this->teamRequest, $result);
    }

    // ------------------------------------------------------------------
    // Team relation
    // ------------------------------------------------------------------

    public function testSetAndGetTeam(): void
    {
        $team = $this->createMock(Team::class);
        $this->teamRequest->setTeam($team);
        $this->assertSame($team, $this->teamRequest->getTeam());
    }

    public function testSetTeamReturnsStatic(): void
    {
        $team = $this->createMock(Team::class);
        $result = $this->teamRequest->setTeam($team);
        $this->assertSame($this->teamRequest, $result);
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testToString(): void
    {
        $team = $this->createMock(Team::class);
        $team->method('getLibTeam')->willReturn('Alpha Team');

        $player = $this->createMock(Player::class);
        $player->method('getPseudo')->willReturn('GamerX');

        $this->teamRequest->setTeam($team);
        $this->teamRequest->setPlayer($player);
        $this->teamRequest->setId(5);

        $this->assertSame('Alpha Team # GamerX [5]', (string) $this->teamRequest);
    }
}
