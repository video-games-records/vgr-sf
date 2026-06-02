<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Unit\Presentation\Twig\Extension;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\Badge;
use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository\PlayerBadgeRepository;
use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository\TeamBadgeRepository;
use App\BoundedContext\VideoGamesRecords\Badge\Presentation\Twig\Extension\BadgeHistoryExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;

class BadgeHistoryExtensionTest extends TestCase
{
    private function makeExtension(
        ?PlayerBadgeRepository $playerRepo = null,
        ?TeamBadgeRepository $teamRepo = null,
    ): BadgeHistoryExtension {
        return new BadgeHistoryExtension(
            $playerRepo ?? $this->createMock(PlayerBadgeRepository::class),
            $teamRepo ?? $this->createMock(TeamBadgeRepository::class),
        );
    }

    public function testGetFunctionsReturnsTwoEntries(): void
    {
        $functions = $this->makeExtension()->getFunctions();

        $this->assertCount(2, $functions);
    }

    public function testGetFunctionsRegistersPlayerBadgeHistoryFunction(): void
    {
        $names = array_map(fn (TwigFunction $f) => $f->getName(), $this->makeExtension()->getFunctions());

        $this->assertContains('vgr_player_badge_history', $names);
    }

    public function testGetFunctionsRegistersTeamBadgeHistoryFunction(): void
    {
        $names = array_map(fn (TwigFunction $f) => $f->getName(), $this->makeExtension()->getFunctions());

        $this->assertContains('vgr_team_badge_history', $names);
    }

    public function testGetPlayerBadgeHistoryDelegatesToRepository(): void
    {
        $badge = $this->createMock(Badge::class);
        $expected = [['pseudo' => 'Alice', 'createdAt' => new \DateTime(), 'endedAt' => null, 'mbOrder' => 1]];

        $playerRepo = $this->createMock(PlayerBadgeRepository::class);
        $playerRepo->expects($this->once())
            ->method('getHistoryForBadge')
            ->with($badge)
            ->willReturn($expected);

        $result = $this->makeExtension($playerRepo)->getPlayerBadgeHistory($badge);

        $this->assertSame($expected, $result);
    }

    public function testGetTeamBadgeHistoryDelegatesToRepository(): void
    {
        $badge = $this->createMock(Badge::class);
        $expected = [['libTeam' => 'Team A', 'createdAt' => new \DateTime(), 'endedAt' => null, 'mbOrder' => null]];

        $teamRepo = $this->createMock(TeamBadgeRepository::class);
        $teamRepo->expects($this->once())
            ->method('getHistoryForBadge')
            ->with($badge)
            ->willReturn($expected);

        $result = $this->makeExtension(null, $teamRepo)->getTeamBadgeHistory($badge);

        $this->assertSame($expected, $result);
    }

    public function testGetPlayerBadgeHistoryReturnsEmptyArrayWhenNone(): void
    {
        $badge = $this->createMock(Badge::class);

        $playerRepo = $this->createMock(PlayerBadgeRepository::class);
        $playerRepo->method('getHistoryForBadge')->willReturn([]);

        $this->assertSame([], $this->makeExtension($playerRepo)->getPlayerBadgeHistory($badge));
    }

    public function testGetTeamBadgeHistoryReturnsEmptyArrayWhenNone(): void
    {
        $badge = $this->createMock(Badge::class);

        $teamRepo = $this->createMock(TeamBadgeRepository::class);
        $teamRepo->method('getHistoryForBadge')->willReturn([]);

        $this->assertSame([], $this->makeExtension(null, $teamRepo)->getTeamBadgeHistory($badge));
    }
}
