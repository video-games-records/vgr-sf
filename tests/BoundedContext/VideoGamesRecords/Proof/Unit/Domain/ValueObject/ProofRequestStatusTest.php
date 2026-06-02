<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Proof\Unit\Domain\ValueObject;

use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\ProofRequestStatus;
use PHPUnit\Framework\TestCase;

class ProofRequestStatusTest extends TestCase
{
    public function testIsInProgressReturnsTrueForInProgress(): void
    {
        $this->assertTrue(ProofRequestStatus::IN_PROGRESS->isInProgress());
    }

    public function testIsInProgressReturnsFalseForOtherStatuses(): void
    {
        $this->assertFalse(ProofRequestStatus::REFUSED->isInProgress());
        $this->assertFalse(ProofRequestStatus::ACCEPTED->isInProgress());
    }

    public function testGetStatusChoicesContainsAllStatuses(): void
    {
        $choices = ProofRequestStatus::getStatusChoices();

        $this->assertArrayHasKey('IN PROGRESS', $choices);
        $this->assertArrayHasKey('REFUSED', $choices);
        $this->assertArrayHasKey('ACCEPTED', $choices);
    }

    public function testGetStatusChoicesHasCorrectCount(): void
    {
        $this->assertCount(3, ProofRequestStatus::getStatusChoices());
    }
}
