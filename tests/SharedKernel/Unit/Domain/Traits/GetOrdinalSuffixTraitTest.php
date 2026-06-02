<?php

declare(strict_types=1);

namespace App\Tests\SharedKernel\Unit\Domain\Traits;

use App\SharedKernel\Domain\Traits\GetOrdinalSuffixTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GetOrdinalSuffixTraitTest extends TestCase
{
    use GetOrdinalSuffixTrait;

    // ------------------------------------------------------------------
    // Edge cases
    // ------------------------------------------------------------------

    public function testZeroReturnsEmptyString(): void
    {
        $this->assertSame('', $this->getOrdinalSuffix(0));
    }

    public function testNegativeReturnsEmptyString(): void
    {
        $this->assertSame('', $this->getOrdinalSuffix(-5));
    }

    // ------------------------------------------------------------------
    // Standard suffixes
    // ------------------------------------------------------------------

    /** @return array<string, array{int, string}> */
    public static function ordinalProvider(): array
    {
        return [
            '1st'  => [1,   'st'],
            '2nd'  => [2,   'nd'],
            '3rd'  => [3,   'rd'],
            '4th'  => [4,   'th'],
            '5th'  => [5,   'th'],
            '10th' => [10,  'th'],
            '11th' => [11,  'th'],
            '12th' => [12,  'th'],
            '13th' => [13,  'th'],
            '14th' => [14,  'th'],
            '20th' => [20,  'th'],
            '21st' => [21,  'st'],
            '22nd' => [22,  'nd'],
            '23rd' => [23,  'rd'],
            '24th' => [24,  'th'],
            '100th' => [100, 'th'],
            '101st' => [101, 'st'],
            '111th' => [111, 'th'],
            '112th' => [112, 'th'],
            '113th' => [113, 'th'],
            '121st' => [121, 'st'],
        ];
    }

    #[DataProvider('ordinalProvider')]
    public function testOrdinalSuffix(int $number, string $expected): void
    {
        $this->assertSame($expected, $this->getOrdinalSuffix($number));
    }
}
