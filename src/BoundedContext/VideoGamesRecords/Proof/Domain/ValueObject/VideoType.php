<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject;

use Webmozart\Assert\Assert;

class VideoType
{
    public const string YOUTUBE = 'Youtube';
    public const string TWITCH = 'Twitch';
    public const string UNKNOWN = 'Unknown';

    public const VALUES = [
        self::YOUTUBE,
        self::TWITCH,
        self::UNKNOWN,
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


    /**
     * @return array<string, string>
     */
    public static function getTypeChoices(): array
    {
        return [
            self::YOUTUBE  => self::YOUTUBE,
            self::TWITCH   => self::TWITCH,
            self::UNKNOWN  => self::UNKNOWN,
        ];
    }
}
