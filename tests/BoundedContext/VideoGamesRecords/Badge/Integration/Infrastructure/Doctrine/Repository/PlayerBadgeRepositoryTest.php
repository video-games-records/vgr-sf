<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Integration\Infrastructure\Doctrine\Repository;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;
use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository\PlayerBadgeRepository;
use App\Tests\BoundedContext\VideoGamesRecords\Badge\Factory\BadgeFactory;
use App\Tests\BoundedContext\VideoGamesRecords\Core\Factory\PlayerFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class PlayerBadgeRepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private PlayerBadgeRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $repository = static::getContainer()->get(PlayerBadgeRepository::class);
        $this->assertInstanceOf(PlayerBadgeRepository::class, $repository);
        $this->repository = $repository;
    }

    public function testAssignSimpleBadgesAssignsOnlyReachedThresholds(): void
    {
        BadgeFactory::new()->withType(BadgeType::DON)->withValue(10)->create();
        BadgeFactory::new()->withType(BadgeType::DON)->withValue(50)->create();
        BadgeFactory::new()->withType(BadgeType::DON)->withValue(100)->create();
        $player = PlayerFactory::new()->with(['donation' => 60])->create();

        $assigned = $this->repository->assignSimpleBadges($player, BadgeType::DON, 60);

        $this->assertSame(2, $assigned);
        $this->assertCount(2, $this->repository->getDonationBadgesDataForPlayer($player));
    }

    public function testAssignSimpleBadgesIsIdempotent(): void
    {
        BadgeFactory::new()->withType(BadgeType::DON)->withValue(10)->create();
        $player = PlayerFactory::new()->with(['donation' => 10])->create();

        $this->repository->assignSimpleBadges($player, BadgeType::DON, 10);
        $assignedAgain = $this->repository->assignSimpleBadges($player, BadgeType::DON, 10);

        $this->assertSame(0, $assignedAgain);
        $this->assertCount(1, $this->repository->getDonationBadgesDataForPlayer($player));
    }

    public function testAssignSimpleBadgesIgnoresOtherBadgeTypes(): void
    {
        BadgeFactory::new()->connection()->withValue(10)->create();
        $player = PlayerFactory::new()->with(['donation' => 60])->create();

        $assigned = $this->repository->assignSimpleBadges($player, BadgeType::DON, 60);

        $this->assertSame(0, $assigned);
    }
}
