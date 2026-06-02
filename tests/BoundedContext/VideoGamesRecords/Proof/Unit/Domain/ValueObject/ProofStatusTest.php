<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Proof\Unit\Domain\ValueObject;

use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\ProofStatus;
use PHPUnit\Framework\TestCase;

class ProofStatusTest extends TestCase
{
    public function testIsInProgressReturnsTrueForInProgress(): void
    {
        $this->assertTrue(ProofStatus::IN_PROGRESS->isInProgress());
    }

    public function testIsInProgressReturnsFalseForOtherStatuses(): void
    {
        $this->assertFalse(ProofStatus::REFUSED->isInProgress());
        $this->assertFalse(ProofStatus::ACCEPTED->isInProgress());
        $this->assertFalse(ProofStatus::CLOSED->isInProgress());
        $this->assertFalse(ProofStatus::DELETED->isInProgress());
    }

    public function testGetStatusChoicesContainsAllExpectedStatuses(): void
    {
        $choices = ProofStatus::getStatusChoices();

        $this->assertArrayHasKey('IN PROGRESS', $choices);
        $this->assertArrayHasKey('REFUSED', $choices);
        $this->assertArrayHasKey('ACCEPTED', $choices);
        $this->assertArrayHasKey('CLOSED', $choices);
    }

    public function testGetStatusChoicesDoesNotContainDeleted(): void
    {
        $choices = ProofStatus::getStatusChoices();

        $this->assertArrayNotHasKey('DELETED', $choices);
    }

    public function testGetStatusChoicesHasCorrectCount(): void
    {
        $this->assertCount(4, ProofStatus::getStatusChoices());
    }
}
