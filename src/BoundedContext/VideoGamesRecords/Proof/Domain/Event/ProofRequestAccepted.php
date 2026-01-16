<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Domain\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\ProofRequest;

class ProofRequestAccepted extends Event
{
    protected ProofRequest $proofRequest;

    public function __construct(ProofRequest $proofRequest)
    {
        $this->proofRequest = $proofRequest;
    }

    public function getProofRequest(): ProofRequest
    {
        return $this->proofRequest;
    }
}
