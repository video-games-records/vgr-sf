<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Unit\Domain\ValueObject;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;
use PHPUnit\Framework\TestCase;

class BadgeTypeTest extends TestCase
{
    public function testGetSpecialBadgesReturnsCorrectCases(): void
    {
        $specials = BadgeType::getSpecialBadges();

        $this->assertContains(BadgeType::INSCRIPTION, $specials);
        $this->assertContains(BadgeType::SPECIAL_WEBMASTER, $specials);
        $this->assertContains(BadgeType::VGR_SPECIAL_COUNTRY, $specials);
        $this->assertContains(BadgeType::VGR_SPECIAL_CUP, $specials);
        $this->assertContains(BadgeType::VGR_SPECIAL_LEGEND, $specials);
        $this->assertContains(BadgeType::VGR_SPECIAL_MEDALS, $specials);
        $this->assertContains(BadgeType::VGR_SPECIAL_POINTS, $specials);
        $this->assertCount(7, $specials);
    }

    public function testGetSpecialBadgesDoesNotContainNonSpecialTypes(): void
    {
        $specials = BadgeType::getSpecialBadges();

        $this->assertNotContains(BadgeType::MASTER, $specials);
        $this->assertNotContains(BadgeType::SERIE, $specials);
        $this->assertNotContains(BadgeType::PLATFORM, $specials);
        $this->assertNotContains(BadgeType::TWITCH, $specials);
    }

    public function testGetSpecialBadgeValuesReturnsStrings(): void
    {
        $values = BadgeType::getSpecialBadgeValues();

        $this->assertContains('Inscription', $values);
        $this->assertContains('SpecialWebmaster', $values);
        $this->assertCount(7, $values);
    }

    public function testIsSpecialReturnsTrueForSpecialBadges(): void
    {
        $this->assertTrue(BadgeType::INSCRIPTION->isSpecial());
        $this->assertTrue(BadgeType::SPECIAL_WEBMASTER->isSpecial());
        $this->assertTrue(BadgeType::VGR_SPECIAL_COUNTRY->isSpecial());
        $this->assertTrue(BadgeType::VGR_SPECIAL_CUP->isSpecial());
        $this->assertTrue(BadgeType::VGR_SPECIAL_LEGEND->isSpecial());
        $this->assertTrue(BadgeType::VGR_SPECIAL_MEDALS->isSpecial());
        $this->assertTrue(BadgeType::VGR_SPECIAL_POINTS->isSpecial());
    }

    public function testIsSpecialReturnsFalseForNonSpecialBadges(): void
    {
        $this->assertFalse(BadgeType::MASTER->isSpecial());
        $this->assertFalse(BadgeType::SERIE->isSpecial());
        $this->assertFalse(BadgeType::PLATFORM->isSpecial());
        $this->assertFalse(BadgeType::CONNEXION->isSpecial());
        $this->assertFalse(BadgeType::TWITCH->isSpecial());
        $this->assertFalse(BadgeType::VGR_CHART->isSpecial());
        $this->assertFalse(BadgeType::VGR_PROOF->isSpecial());
    }

    public function testGetDefaultDirectoryReturnsBadge(): void
    {
        $this->assertSame('badge', BadgeType::getDefaultDirectory());
    }

    public function testGetDirectoryForSerie(): void
    {
        $this->assertSame('series/badge', BadgeType::SERIE->getDirectory());
    }

    public function testGetDirectoryForOtherTypesUsesBadgePrefix(): void
    {
        $this->assertSame('badge' . DIRECTORY_SEPARATOR . 'Master', BadgeType::MASTER->getDirectory());
        $this->assertSame('badge' . DIRECTORY_SEPARATOR . 'Platform', BadgeType::PLATFORM->getDirectory());
        $this->assertSame('badge' . DIRECTORY_SEPARATOR . 'Twitch', BadgeType::TWITCH->getDirectory());
    }

    public function testGetDirectoryIsConsistentWithValue(): void
    {
        foreach (BadgeType::cases() as $case) {
            if ($case === BadgeType::SERIE) {
                continue;
            }
            $this->assertStringStartsWith('badge', $case->getDirectory());
        }
    }
}
