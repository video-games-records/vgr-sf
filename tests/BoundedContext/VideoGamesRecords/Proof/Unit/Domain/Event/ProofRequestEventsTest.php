<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Proof\Unit\Domain\Event;

use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\ProofRequest;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Event\ProofRequestAccepted;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Event\ProofRequestRefused;
use PHPUnit\Framework\TestCase;

class ProofRequestEventsTest extends TestCase
{
    public function testProofRequestAcceptedHoldsProofRequest(): void
    {
        $proofRequest = $this->createMock(ProofRequest::class);
        $event = new ProofRequestAccepted($proofRequest);

        $this->assertSame($proofRequest, $event->getProofRequest());
    }

    public function testProofRequestRefusedHoldsProofRequest(): void
    {
        $proofRequest = $this->createMock(ProofRequest::class);
        $event = new ProofRequestRefused($proofRequest);

        $this->assertSame($proofRequest, $event->getProofRequest());
    }

    public function testAcceptedAndRefusedHoldIndependentRequests(): void
    {
        $req1 = $this->createMock(ProofRequest::class);
        $req2 = $this->createMock(ProofRequest::class);

        $this->assertSame($req1, (new ProofRequestAccepted($req1))->getProofRequest());
        $this->assertSame($req2, (new ProofRequestRefused($req2))->getProofRequest());
        $this->assertNotSame($req1, $req2);
    }
}
