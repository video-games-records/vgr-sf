<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Video\Unit\Domain\ValueObject;

use App\BoundedContext\VideoGamesRecords\Video\Domain\ValueObject\VideoType;
use PHPUnit\Framework\TestCase;

class VideoTypeTest extends TestCase
{
    public function testGetTypeChoicesContainsAllTypes(): void
    {
        $choices = VideoType::getTypeChoices();

        $this->assertArrayHasKey('Youtube', $choices);
        $this->assertArrayHasKey('Twitch', $choices);
        $this->assertArrayHasKey('Unknown', $choices);
    }

    public function testGetTypeChoicesHasCorrectCount(): void
    {
        $this->assertCount(3, VideoType::getTypeChoices());
    }

    public function testEnumValuesMatchExpected(): void
    {
        $this->assertSame('Youtube', VideoType::YOUTUBE->value);
        $this->assertSame('Twitch', VideoType::TWITCH->value);
        $this->assertSame('Unknown', VideoType::UNKNOWN->value);
    }

    public function testFromValueReturnsCorrectCase(): void
    {
        $this->assertSame(VideoType::YOUTUBE, VideoType::from('Youtube'));
        $this->assertSame(VideoType::TWITCH, VideoType::from('Twitch'));
        $this->assertSame(VideoType::UNKNOWN, VideoType::from('Unknown'));
    }
}
