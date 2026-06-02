<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Proof\Unit\Domain\Event;

use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Proof;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Event\ProofAccepted;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Event\ProofRefused;
use PHPUnit\Framework\TestCase;

class ProofEventsTest extends TestCase
{
    public function testProofAcceptedHoldsProof(): void
    {
        $proof = $this->createMock(Proof::class);
        $event = new ProofAccepted($proof);

        $this->assertSame($proof, $event->getProof());
    }

    public function testProofRefusedHoldsProof(): void
    {
        $proof = $this->createMock(Proof::class);
        $event = new ProofRefused($proof);

        $this->assertSame($proof, $event->getProof());
    }

    public function testProofAcceptedAndRefusedHoldIndependentProofs(): void
    {
        $accepted = $this->createMock(Proof::class);
        $refused = $this->createMock(Proof::class);

        $this->assertSame($accepted, (new ProofAccepted($accepted))->getProof());
        $this->assertSame($refused, (new ProofRefused($refused))->getProof());
        $this->assertNotSame($accepted, $refused);
    }
}
