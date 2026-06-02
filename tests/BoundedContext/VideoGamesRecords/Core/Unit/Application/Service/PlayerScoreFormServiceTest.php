<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Application\Service;

use App\BoundedContext\VideoGamesRecords\Core\Application\Service\PlayerScoreFormService;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\ChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlatformRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[AllowMockObjectsWithoutExpectations]
class PlayerScoreFormServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private ChartRepository&MockObject $chartRepository;
    private PlatformRepository&MockObject $platformRepository;
    private MessageBusInterface&MockObject $messageBus;
    private PlayerScoreFormService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->chartRepository = $this->createMock(ChartRepository::class);
        $this->platformRepository = $this->createMock(PlatformRepository::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);

        $this->service = new PlayerScoreFormService(
            $this->em,
            $this->chartRepository,
            $this->platformRepository,
            $this->messageBus
        );
    }

    public function testProcessSubmissionSkipsEntriesWithoutModifiedKey(): void
    {
        $player = $this->createMock(Player::class);
        $formData = [
            123 => ['libs' => [1 => ['values' => ['1000']]]],
        ];

        $this->chartRepository->expects($this->never())->method('find');
        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->never())->method('flush');

        $result = $this->service->processSubmission($player, $formData);

        $this->assertSame(0, $result['created']);
        $this->assertSame(0, $result['updated']);
        $this->assertSame([], $result['chartIds']);
    }

    public function testProcessSubmissionSkipsEntriesWithEmptyLibs(): void
    {
        $player = $this->createMock(Player::class);
        $formData = [
            123 => ['modified' => '1', 'libs' => []],
        ];

        $this->chartRepository->expects($this->never())->method('find');
        $this->em->expects($this->never())->method('flush');

        $result = $this->service->processSubmission($player, $formData);

        $this->assertSame(0, $result['created']);
        $this->assertSame(0, $result['updated']);
        $this->assertSame([], $result['chartIds']);
    }

    public function testProcessSubmissionSkipsEntriesWithNoLibsKey(): void
    {
        $player = $this->createMock(Player::class);
        $formData = [
            123 => ['modified' => '1'],
        ];

        $this->chartRepository->expects($this->never())->method('find');

        $result = $this->service->processSubmission($player, $formData);

        $this->assertSame(0, $result['created']);
        $this->assertSame(0, $result['updated']);
    }

    public function testProcessSubmissionSkipsEntriesWithAllEmptyValues(): void
    {
        $player = $this->createMock(Player::class);
        $formData = [
            123 => ['modified' => '1', 'libs' => [1 => ['values' => ['', '']]]],
        ];

        $this->chartRepository->expects($this->never())->method('find');
        $this->em->expects($this->never())->method('flush');

        $result = $this->service->processSubmission($player, $formData);

        $this->assertSame(0, $result['created']);
        $this->assertSame(0, $result['updated']);
    }

    public function testProcessSubmissionSkipsEntriesWhenChartNotFound(): void
    {
        $player = $this->createMock(Player::class);
        $formData = [
            999 => ['modified' => '1', 'libs' => [1 => ['values' => ['1000']]]],
        ];

        $this->chartRepository->method('find')->with(999)->willReturn(null);
        $this->em->expects($this->never())->method('flush');

        $result = $this->service->processSubmission($player, $formData);

        $this->assertSame(0, $result['created']);
        $this->assertSame(0, $result['updated']);
    }

    public function testProcessSubmissionReturnsEmptyWhenNoFormData(): void
    {
        $player = $this->createMock(Player::class);

        $result = $this->service->processSubmission($player, []);

        $this->assertSame(0, $result['created']);
        $this->assertSame(0, $result['updated']);
        $this->assertSame([], $result['chartIds']);
    }

    public function testProcessSubmissionCreatesNewPlayerChartWhenChartFound(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getId')->willReturn(1);

        $chart = $this->createMock(Chart::class);
        $chart->method('getPlayerCharts')->willReturn(new ArrayCollection([]));
        $chart->method('getLibs')->willReturn(new ArrayCollection([]));

        $this->chartRepository->method('find')->with(10)->willReturn($chart);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');
        $this->messageBus->expects($this->once())->method('dispatch')
            ->willReturn(new Envelope(new \stdClass()));

        $result = $this->service->processSubmission($player, [
            10 => ['modified' => '1', 'libs' => [1 => ['values' => ['5000']]]],
        ]);

        $this->assertSame(1, $result['created']);
        $this->assertSame(0, $result['updated']);
        $this->assertSame([10], $result['chartIds']);
    }

    public function testProcessSubmissionHandlesMultipleEntries(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getId')->willReturn(1);

        $chart1 = $this->createMock(Chart::class);
        $chart1->method('getPlayerCharts')->willReturn(new ArrayCollection([]));
        $chart1->method('getLibs')->willReturn(new ArrayCollection([]));

        $chart2 = $this->createMock(Chart::class);
        $chart2->method('getPlayerCharts')->willReturn(new ArrayCollection([]));
        $chart2->method('getLibs')->willReturn(new ArrayCollection([]));

        $this->chartRepository->method('find')
            ->willReturnMap([
                [10, $chart1],
                [20, $chart2],
                [30, null],
            ]);

        $this->messageBus->method('dispatch')->willReturn(new Envelope(new \stdClass()));

        $result = $this->service->processSubmission($player, [
            10 => ['modified' => '1', 'libs' => [1 => ['values' => ['100']]]],
            20 => ['modified' => '1', 'libs' => [1 => ['values' => ['200']]]],
            30 => ['modified' => '1', 'libs' => [1 => ['values' => ['300']]]],
        ]);

        $this->assertSame(2, $result['created']);
        $this->assertSame(0, $result['updated']);
        $this->assertCount(2, $result['chartIds']);
    }
}
