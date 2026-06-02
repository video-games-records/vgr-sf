<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Shared\Unit\Domain\Tools;

use App\BoundedContext\VideoGamesRecords\Shared\Domain\Tools\ScoreTools;
use PHPUnit\Framework\TestCase;

class ScoreToolsTest extends TestCase
{
    // ------------------------------------------------------------------
    // parseChartMask
    // ------------------------------------------------------------------

    public function testParseChartMaskSinglePart(): void
    {
        $result = ScoreTools::parseChartMask('10~pts');

        $this->assertCount(1, $result);
        $this->assertSame(10, $result[0]['size']);
        $this->assertSame('pts', $result[0]['suffixe']);
    }

    public function testParseChartMaskMultipleParts(): void
    {
        $result = ScoreTools::parseChartMask('3~:|2~');

        $this->assertCount(2, $result);
        $this->assertSame(3, $result[0]['size']);
        $this->assertSame(':', $result[0]['suffixe']);
        $this->assertSame(2, $result[1]['size']);
        $this->assertSame('', $result[1]['suffixe']);
    }

    public function testParseChartMaskThreeParts(): void
    {
        $result = ScoreTools::parseChartMask('2~h|2~m|2~s');

        $this->assertCount(3, $result);
        $this->assertSame('h', $result[0]['suffixe']);
        $this->assertSame('m', $result[1]['suffixe']);
        $this->assertSame('s', $result[2]['suffixe']);
    }

    // ------------------------------------------------------------------
    // formatScore
    // ------------------------------------------------------------------

    public function testFormatScoreWithNullReturnsEmptyString(): void
    {
        $this->assertSame('', ScoreTools::formatScore(null, '10~'));
    }

    public function testFormatScoreSimpleMask(): void
    {
        $this->assertSame('42', ScoreTools::formatScore(42, '10~'));
    }

    public function testFormatScoreTimeMask(): void
    {
        // 130 → "1:30" with mask "3~:|2~"
        $this->assertSame('1:30', ScoreTools::formatScore(130, '3~:|2~'));
    }

    public function testFormatScoreNegativeValue(): void
    {
        $result = ScoreTools::formatScore('-42', '10~');

        $this->assertStringStartsWith('-', $result);
    }

    public function testFormatScoreZero(): void
    {
        $this->assertSame('0', ScoreTools::formatScore(0, '10~'));
    }

    // ------------------------------------------------------------------
    // formToBdd
    // ------------------------------------------------------------------

    public function testFormToBddReturnsNullWhenAllValuesEmpty(): void
    {
        $this->assertNull(ScoreTools::formToBdd('3~:|2~', [['value' => ''], ['value' => '']]));
    }

    public function testFormToBddSingleInputReturnsValue(): void
    {
        $result = ScoreTools::formToBdd('10~', [['value' => '42']]);

        $this->assertSame('42', $result);
    }

    public function testFormToBddMultiplePartsCombiningsValues(): void
    {
        $result = ScoreTools::formToBdd('3~:|2~', [['value' => '1'], ['value' => '30']]);

        $this->assertSame('130', $result);
    }

    // ------------------------------------------------------------------
    // getValues (round-trip with formToBdd)
    // ------------------------------------------------------------------

    public function testGetValuesRoundTripWithFormToBdd(): void
    {
        $mask = '3~:|2~';
        $original = '130';

        $values = ScoreTools::getValues($mask, $original);
        $result = ScoreTools::formToBdd($mask, $values);

        $this->assertSame($original, $result);
    }

    public function testGetValuesWithNullReturnsEmptyValues(): void
    {
        $values = ScoreTools::getValues('3~:|2~', null);

        foreach ($values as $v) {
            $this->assertSame('', $v['value']);
        }
    }
}
