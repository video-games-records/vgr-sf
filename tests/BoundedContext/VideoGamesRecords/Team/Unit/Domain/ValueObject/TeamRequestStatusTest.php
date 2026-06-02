<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Team\Unit\Domain\ValueObject;

use App\BoundedContext\VideoGamesRecords\Team\Domain\ValueObject\TeamRequestStatus;
use PHPUnit\Framework\TestCase;

class TeamRequestStatusTest extends TestCase
{
    public function testIsActiveReturnsTrueOnlyForActive(): void
    {
        $this->assertTrue(TeamRequestStatus::ACTIVE->isActive());
        $this->assertFalse(TeamRequestStatus::ACCEPTED->isActive());
        $this->assertFalse(TeamRequestStatus::CANCELED->isActive());
        $this->assertFalse(TeamRequestStatus::REFUSED->isActive());
    }

    public function testIsAcceptedReturnsTrueOnlyForAccepted(): void
    {
        $this->assertFalse(TeamRequestStatus::ACTIVE->isAccepted());
        $this->assertTrue(TeamRequestStatus::ACCEPTED->isAccepted());
        $this->assertFalse(TeamRequestStatus::CANCELED->isAccepted());
        $this->assertFalse(TeamRequestStatus::REFUSED->isAccepted());
    }

    public function testIsRefusedReturnsTrueOnlyForRefused(): void
    {
        $this->assertFalse(TeamRequestStatus::ACTIVE->isRefused());
        $this->assertFalse(TeamRequestStatus::ACCEPTED->isRefused());
        $this->assertFalse(TeamRequestStatus::CANCELED->isRefused());
        $this->assertTrue(TeamRequestStatus::REFUSED->isRefused());
    }

    public function testIsCanceledReturnsTrueOnlyForCanceled(): void
    {
        $this->assertFalse(TeamRequestStatus::ACTIVE->isCanceled());
        $this->assertFalse(TeamRequestStatus::ACCEPTED->isCanceled());
        $this->assertTrue(TeamRequestStatus::CANCELED->isCanceled());
        $this->assertFalse(TeamRequestStatus::REFUSED->isCanceled());
    }

    public function testGetStatusChoicesContainsAllStatuses(): void
    {
        $choices = TeamRequestStatus::getStatusChoices();

        $this->assertArrayHasKey('ACTIVE', $choices);
        $this->assertArrayHasKey('ACCEPTED', $choices);
        $this->assertArrayHasKey('REFUSED', $choices);
        $this->assertArrayHasKey('CANCELED', $choices);
        $this->assertCount(4, $choices);
    }
}
