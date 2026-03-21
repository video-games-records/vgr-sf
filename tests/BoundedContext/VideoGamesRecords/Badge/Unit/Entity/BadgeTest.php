<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\Badge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\MasterBadge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BadgeTest extends TestCase
{
    private Badge $badge;

    protected function setUp(): void
    {
        $this->badge = new Badge();
    }

    // ------------------------------------------------------------------
    // Basic properties – defaults
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->badge->getId());
    }

    public function testValueDefaultsToZero(): void
    {
        $this->assertSame(0, $this->badge->getValue());
    }

    public function testNbPlayerDefaultsToZero(): void
    {
        $this->assertSame(0, $this->badge->getNbPlayer());
    }

    // ------------------------------------------------------------------
    // Getters / setters
    // ------------------------------------------------------------------

    public function testSetAndGetId(): void
    {
        $result = $this->badge->setId(5);
        $this->assertSame(5, $this->badge->getId());
        $this->assertSame($this->badge, $result);
    }

    public function testSetAndGetType(): void
    {
        $result = $this->badge->setType(BadgeType::MASTER);
        $this->assertSame(BadgeType::MASTER, $this->badge->getType());
        $this->assertSame($this->badge, $result);
    }

    public function testSetAndGetPicture(): void
    {
        $result = $this->badge->setPicture('gold.gif');
        $this->assertSame('gold.gif', $this->badge->getPicture());
        $this->assertSame($this->badge, $result);
    }

    public function testSetAndGetValue(): void
    {
        $result = $this->badge->setValue(250);
        $this->assertSame(250, $this->badge->getValue());
        $this->assertSame($this->badge, $result);
    }

    public function testSetAndGetNbPlayer(): void
    {
        $result = $this->badge->setNbPlayer(10);
        $this->assertSame(10, $this->badge->getNbPlayer());
        $this->assertSame($this->badge, $result);
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToString(): void
    {
        $this->badge->setType(BadgeType::FORUM);
        $this->badge->setPicture('forum.gif');
        $this->badge->setId(99);

        $this->assertSame('Forum / forum.gif [99]', (string) $this->badge);
    }

    // ------------------------------------------------------------------
    // majValue (base class: no-op)
    // ------------------------------------------------------------------

    public function testMajValueDoesNotChangeValueOnBaseClass(): void
    {
        $this->badge->setValue(42);
        $this->badge->majValue();
        $this->assertSame(42, $this->badge->getValue());
    }

    public function testMajValueWithGameArgumentDoesNotChangeValueOnBaseClass(): void
    {
        $game = $this->createMock(Game::class);
        $this->badge->setValue(42);
        $this->badge->majValue($game);
        $this->assertSame(42, $this->badge->getValue());
    }

    // ------------------------------------------------------------------
    // isTypeMaster
    // ------------------------------------------------------------------

    public function testIsTypeMasterReturnsFalseForBaseBadge(): void
    {
        $this->assertFalse($this->badge->isTypeMaster());
    }

    public function testIsTypeMasterReturnsTrueForMasterBadge(): void
    {
        $masterBadge = new MasterBadge();
        $this->assertTrue($masterBadge->isTypeMaster());
    }

    // ------------------------------------------------------------------
    // BadgeType::getSpecialBadges / isSpecial
    // ------------------------------------------------------------------

    #[DataProvider('specialBadgeTypeProvider')]
    public function testIsSpecialReturnsTrueForSpecialTypes(BadgeType $type): void
    {
        $this->assertTrue($type->isSpecial());
    }

    /**
     * @return array<string, array{BadgeType}>
     */
    public static function specialBadgeTypeProvider(): array
    {
        return [
            'INSCRIPTION'        => [BadgeType::INSCRIPTION],
            'SPECIAL_WEBMASTER'  => [BadgeType::SPECIAL_WEBMASTER],
            'VGR_SPECIAL_COUNTRY'=> [BadgeType::VGR_SPECIAL_COUNTRY],
            'VGR_SPECIAL_CUP'    => [BadgeType::VGR_SPECIAL_CUP],
            'VGR_SPECIAL_LEGEND' => [BadgeType::VGR_SPECIAL_LEGEND],
            'VGR_SPECIAL_MEDALS' => [BadgeType::VGR_SPECIAL_MEDALS],
            'VGR_SPECIAL_POINTS' => [BadgeType::VGR_SPECIAL_POINTS],
        ];
    }

    #[DataProvider('nonSpecialBadgeTypeProvider')]
    public function testIsSpecialReturnsFalseForNonSpecialTypes(BadgeType $type): void
    {
        $this->assertFalse($type->isSpecial());
    }

    /**
     * @return array<string, array{BadgeType}>
     */
    public static function nonSpecialBadgeTypeProvider(): array
    {
        return [
            'CONNEXION' => [BadgeType::CONNEXION],
            'DON'       => [BadgeType::DON],
            'FORUM'     => [BadgeType::FORUM],
            'MASTER'    => [BadgeType::MASTER],
            'PLATFORM'  => [BadgeType::PLATFORM],
            'SERIE'     => [BadgeType::SERIE],
            'VGR_CHART' => [BadgeType::VGR_CHART],
            'VGR_PROOF' => [BadgeType::VGR_PROOF],
            'TWITCH'    => [BadgeType::TWITCH],
        ];
    }

    // ------------------------------------------------------------------
    // BadgeType::getDirectory
    // ------------------------------------------------------------------

    public function testGetDirectoryForSerieType(): void
    {
        $this->assertSame('series/badge', BadgeType::SERIE->getDirectory());
    }

    public function testGetDirectoryForMasterType(): void
    {
        $this->assertSame('badge' . DIRECTORY_SEPARATOR . 'Master', BadgeType::MASTER->getDirectory());
    }

    public function testGetDirectoryForForumType(): void
    {
        $this->assertSame('badge' . DIRECTORY_SEPARATOR . 'Forum', BadgeType::FORUM->getDirectory());
    }

    public function testGetDefaultDirectory(): void
    {
        $this->assertSame('badge', BadgeType::getDefaultDirectory());
    }
}
