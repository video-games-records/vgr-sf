<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Domain\ValueObject;

use Webmozart\Assert\Assert;

class Target
{
    public const string GAME = 'game';
    public const string PLAYER = 'PLAYER';
    public const string TEAM = 'TEAM';

    public const VALUES = [
        self::GAME,
        self::PLAYER,
        self::TEAM,
    ];

    private string $value;

    public function __construct(string $value)
    {
        self::inArray($value);
        $this->value = $value;
    }

    public static function inArray(string $value): void
    {
        Assert::inArray($value, self::VALUES);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
