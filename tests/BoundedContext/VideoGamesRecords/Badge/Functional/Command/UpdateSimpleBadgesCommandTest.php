<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Functional\Command;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;
use App\BoundedContext\VideoGamesRecords\Badge\Infrastructure\Doctrine\Repository\PlayerBadgeRepository;
use App\Tests\BoundedContext\VideoGamesRecords\Badge\Factory\BadgeFactory;
use App\Tests\BoundedContext\VideoGamesRecords\Core\Factory\PlayerFactory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UpdateSimpleBadgesCommandTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private KernelInterface $testKernel;

    protected function setUp(): void
    {
        $this->testKernel = self::bootKernel();
    }

    public function testCommandAssignsDonBadgesToPlayersWhoReachedTheThreshold(): void
    {
        BadgeFactory::new()->withType(BadgeType::DON)->withValue(10)->create();
        BadgeFactory::new()->withType(BadgeType::DON)->withValue(50)->create();
        $player = PlayerFactory::new()->with(['donation' => 20])->create();

        $application = new Application($this->testKernel);
        $command = $application->find('vgr:badge:update-simple');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Don', $commandTester->getDisplay());

        $playerBadgeRepository = static::getContainer()->get(PlayerBadgeRepository::class);
        $this->assertInstanceOf(PlayerBadgeRepository::class, $playerBadgeRepository);
        $this->assertCount(1, $playerBadgeRepository->getDonationBadgesDataForPlayer($player));
    }
}
