<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Domain\ValueObject;

use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerStatusEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PlayerStatusEnumTest extends TestCase
{
    // ------------------------------------------------------------------
    // getLabel
    // ------------------------------------------------------------------

    public function testGetLabelReturnsNonEmptyStringForAllCases(): void
    {
        foreach (PlayerStatusEnum::cases() as $case) {
            $this->assertNotEmpty($case->getLabel(), "getLabel() for {$case->name} should not be empty");
        }
    }

    public function testGetLabelMemberReturnsExpected(): void
    {
        $this->assertSame('Member', PlayerStatusEnum::MEMBER->getLabel());
    }

    public function testGetLabelAdministratorReturnsExpected(): void
    {
        $this->assertSame('Administrator', PlayerStatusEnum::ADMINISTRATOR->getLabel());
    }

    // ------------------------------------------------------------------
    // getFrenchLabel
    // ------------------------------------------------------------------

    public function testGetFrenchLabelReturnsNonEmptyStringForAllCases(): void
    {
        foreach (PlayerStatusEnum::cases() as $case) {
            $this->assertNotEmpty($case->getFrenchLabel(), "getFrenchLabel() for {$case->name} should not be empty");
        }
    }

    public function testGetFrenchLabelMemberReturnsMembre(): void
    {
        $this->assertSame('Membre', PlayerStatusEnum::MEMBER->getFrenchLabel());
    }

    // ------------------------------------------------------------------
    // getClass
    // ------------------------------------------------------------------

    public function testGetClassReturnsNonEmptyStringForAllCases(): void
    {
        foreach (PlayerStatusEnum::cases() as $case) {
            $this->assertNotEmpty($case->getClass(), "getClass() for {$case->name} should not be empty");
        }
    }

    public function testGetClassMemberReturnsMember(): void
    {
        $this->assertSame('member', PlayerStatusEnum::MEMBER->getClass());
    }

    // ------------------------------------------------------------------
    // isAdmin
    // ------------------------------------------------------------------

    #[DataProvider('adminStatusProvider')]
    public function testIsAdminReturnsTrueForAdminStatuses(PlayerStatusEnum $status): void
    {
        $this->assertTrue($status->isAdmin());
    }

    /**
     * @return array<string, array{PlayerStatusEnum}>
     */
    public static function adminStatusProvider(): array
    {
        return [
            'WEBMASTER'           => [PlayerStatusEnum::WEBMASTER],
            'ADMINISTRATOR'       => [PlayerStatusEnum::ADMINISTRATOR],
            'PROOF_ADMIN'         => [PlayerStatusEnum::PROOF_ADMIN],
            'GAME_AND_PROOF_ADMIN' => [PlayerStatusEnum::GAME_AND_PROOF_ADMIN],
            'CHIEF_PROOF_ADMIN'   => [PlayerStatusEnum::CHIEF_PROOF_ADMIN],
            'CHIEF_STAFF'         => [PlayerStatusEnum::CHIEF_STAFF],
        ];
    }

    public function testIsAdminReturnsFalseForMember(): void
    {
        $this->assertFalse(PlayerStatusEnum::MEMBER->isAdmin());
        $this->assertFalse(PlayerStatusEnum::MODERATOR->isAdmin());
        $this->assertFalse(PlayerStatusEnum::TRANSLATOR->isAdmin());
    }

    // ------------------------------------------------------------------
    // isModerator
    // ------------------------------------------------------------------

    public function testIsModeratorReturnsTrueForExpectedStatuses(): void
    {
        $this->assertTrue(PlayerStatusEnum::MODERATOR->isModerator());
        $this->assertTrue(PlayerStatusEnum::ADMINISTRATOR->isModerator());
        $this->assertTrue(PlayerStatusEnum::CHIEF_STAFF->isModerator());
    }

    public function testIsModeratorReturnsFalseForMember(): void
    {
        $this->assertFalse(PlayerStatusEnum::MEMBER->isModerator());
    }

    // ------------------------------------------------------------------
    // canManageProofs
    // ------------------------------------------------------------------

    public function testCanManageProofsReturnsTrueForExpectedStatuses(): void
    {
        $this->assertTrue(PlayerStatusEnum::PROOF_ADMIN->canManageProofs());
        $this->assertTrue(PlayerStatusEnum::REFEREE->canManageProofs());
        $this->assertTrue(PlayerStatusEnum::GAME_AND_PROOF_ADMIN->canManageProofs());
        $this->assertTrue(PlayerStatusEnum::CHIEF_PROOF_ADMIN->canManageProofs());
        $this->assertTrue(PlayerStatusEnum::CHIEF_STAFF->canManageProofs());
    }

    public function testCanManageProofsReturnsFalseForMember(): void
    {
        $this->assertFalse(PlayerStatusEnum::MEMBER->canManageProofs());
    }

    // ------------------------------------------------------------------
    // canManageGames
    // ------------------------------------------------------------------

    public function testCanManageGamesReturnsTrueForExpectedStatuses(): void
    {
        $this->assertTrue(PlayerStatusEnum::GAME_ADDER->canManageGames());
        $this->assertTrue(PlayerStatusEnum::GAME_AND_PROOF_ADMIN->canManageGames());
        $this->assertTrue(PlayerStatusEnum::ADMINISTRATOR->canManageGames());
        $this->assertTrue(PlayerStatusEnum::CHIEF_STAFF->canManageGames());
    }

    public function testCanManageGamesReturnsFalseForMember(): void
    {
        $this->assertFalse(PlayerStatusEnum::MEMBER->canManageGames());
    }

    // ------------------------------------------------------------------
    // getAllStatuses
    // ------------------------------------------------------------------

    public function testGetAllStatusesReturns15Cases(): void
    {
        $this->assertCount(15, PlayerStatusEnum::getAllStatuses());
    }

    // ------------------------------------------------------------------
    // fromId
    // ------------------------------------------------------------------

    #[DataProvider('fromIdProvider')]
    public function testFromIdReturnsExpectedStatus(int $id, PlayerStatusEnum $expected): void
    {
        $this->assertSame($expected, PlayerStatusEnum::fromId($id));
    }

    /**
     * @return array<string, array{int, PlayerStatusEnum}>
     */
    public static function fromIdProvider(): array
    {
        return [
            'id 1 → MEMBER'       => [1, PlayerStatusEnum::MEMBER],
            'id 8 → ADMINISTRATOR' => [8, PlayerStatusEnum::ADMINISTRATOR],
            'id 15 → STREAMER'    => [15, PlayerStatusEnum::STREAMER],
        ];
    }

    public function testFromIdReturnsNullForUnknownId(): void
    {
        $this->assertNull(PlayerStatusEnum::fromId(0));
        $this->assertNull(PlayerStatusEnum::fromId(99));
    }
}
