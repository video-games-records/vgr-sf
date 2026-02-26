<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Application\Message;

class UpdateGame
{
    public readonly float $timestamp;

    public function __construct()
    {
        $this->timestamp = microtime(true);
    }
}
