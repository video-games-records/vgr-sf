<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Domain\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Proof;

class ProofAccepted extends Event
{
    protected Proof $proof;

    public function __construct(Proof $proof)
    {
        $this->proof = $proof;
    }

    public function getProof(): Proof
    {
        return $this->proof;
    }
}
