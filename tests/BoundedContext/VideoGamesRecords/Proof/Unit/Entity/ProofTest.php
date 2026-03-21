<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Proof\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Picture;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Proof;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\ProofRequest;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Video;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\ProofStatus;
use DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProofTest extends TestCase
{
    private Proof $proof;

    protected function setUp(): void
    {
        $this->proof = new Proof();
    }

    // ------------------------------------------------------------------
    // Basic properties — defaults
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->proof->getId());
    }

    public function testPictureDefaultsToNull(): void
    {
        $this->assertNull($this->proof->getPicture());
    }

    public function testVideoDefaultsToNull(): void
    {
        $this->assertNull($this->proof->getVideo());
    }

    public function testProofRequestDefaultsToNull(): void
    {
        $this->assertNull($this->proof->getProofRequest());
    }

    public function testStatusDefaultsToInProgress(): void
    {
        $this->assertSame(ProofStatus::IN_PROGRESS, $this->proof->getStatus()->getValue());
    }

    public function testResponseDefaultsToNull(): void
    {
        $this->assertNull($this->proof->getResponse());
    }

    public function testPlayerRespondingDefaultsToNull(): void
    {
        $this->assertNull($this->proof->getPlayerResponding());
    }

    public function testCheckedAtDefaultsToNull(): void
    {
        $this->assertNull($this->proof->getCheckedAt());
    }

    public function testPlayerChartDefaultsToNull(): void
    {
        $this->assertNull($this->proof->getPlayerChart());
    }

    // ------------------------------------------------------------------
    // Getters / setters
    // ------------------------------------------------------------------

    public function testSetAndGetId(): void
    {
        $result = $this->proof->setId(42);
        $this->assertSame(42, $this->proof->getId());
        $this->assertSame($this->proof, $result);
    }

    public function testSetAndGetPicture(): void
    {
        $picture = $this->createMock(Picture::class);
        $result = $this->proof->setPicture($picture);
        $this->assertSame($picture, $this->proof->getPicture());
        $this->assertSame($this->proof, $result);
    }

    public function testSetAndGetVideo(): void
    {
        $video = $this->createMock(Video::class);
        $result = $this->proof->setVideo($video);
        $this->assertSame($video, $this->proof->getVideo());
        $this->assertSame($this->proof, $result);
    }

    public function testSetAndGetProofRequest(): void
    {
        $proofRequest = $this->createMock(ProofRequest::class);
        $result = $this->proof->setProofRequest($proofRequest);
        $this->assertSame($proofRequest, $this->proof->getProofRequest());
        $this->assertSame($this->proof, $result);
    }

    public function testSetProofRequestToNull(): void
    {
        $proofRequest = $this->createMock(ProofRequest::class);
        $this->proof->setProofRequest($proofRequest);
        $this->proof->setProofRequest(null);
        $this->assertNull($this->proof->getProofRequest());
    }

    public function testSetAndGetResponse(): void
    {
        $result = $this->proof->setResponse('Score accepted.');
        $this->assertSame('Score accepted.', $this->proof->getResponse());
        $this->assertSame($this->proof, $result);
    }

    public function testSetAndGetPlayer(): void
    {
        $player = $this->createMock(Player::class);
        $result = $this->proof->setPlayer($player);
        $this->assertSame($player, $this->proof->getPlayer());
        $this->assertSame($this->proof, $result);
    }

    public function testSetAndGetPlayerResponding(): void
    {
        $player = $this->createMock(Player::class);
        $result = $this->proof->setPlayerResponding($player);
        $this->assertSame($player, $this->proof->getPlayerResponding());
        $this->assertSame($this->proof, $result);
    }

    public function testSetPlayerRespondingToNull(): void
    {
        $player = $this->createMock(Player::class);
        $this->proof->setPlayerResponding($player);
        $this->proof->setPlayerResponding(null);
        $this->assertNull($this->proof->getPlayerResponding());
    }

    public function testSetAndGetChart(): void
    {
        $chart = $this->createMock(Chart::class);
        $result = $this->proof->setChart($chart);
        $this->assertSame($chart, $this->proof->getChart());
        $this->assertSame($this->proof, $result);
    }

    public function testSetAndGetCheckedAt(): void
    {
        $date = new DateTime('2025-03-15 10:00:00');
        $result = $this->proof->setCheckedAt($date);
        $this->assertSame($date, $this->proof->getCheckedAt());
        $this->assertSame($this->proof, $result);
    }

    // ------------------------------------------------------------------
    // Status value object
    // ------------------------------------------------------------------

    #[DataProvider('statusValuesProvider')]
    public function testSetStatusStoresCorrectValue(string $statusValue): void
    {
        $this->proof->setStatus($statusValue);
        $this->assertSame($statusValue, $this->proof->getStatus()->getValue());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function statusValuesProvider(): array
    {
        return [
            'IN_PROGRESS' => [ProofStatus::IN_PROGRESS],
            'REFUSED'     => [ProofStatus::REFUSED],
            'ACCEPTED'    => [ProofStatus::ACCEPTED],
            'CLOSED'      => [ProofStatus::CLOSED],
            'DELETED'     => [ProofStatus::DELETED],
        ];
    }

    public function testGetStatusReturnsProofStatusValueObject(): void
    {
        $this->proof->setStatus(ProofStatus::ACCEPTED);
        $status = $this->proof->getStatus();
        $this->assertInstanceOf(ProofStatus::class, $status);
        $this->assertSame(ProofStatus::ACCEPTED, $status->getValue());
    }

    // ------------------------------------------------------------------
    // getType()
    // ------------------------------------------------------------------

    public function testGetTypeReturnsPictureWhenPictureIsSet(): void
    {
        $picture = $this->createMock(Picture::class);
        $this->proof->setPicture($picture);
        $this->assertSame('Picture', $this->proof->getType());
    }

    public function testGetTypeReturnsVideoWhenNoPicture(): void
    {
        $this->assertSame('Video', $this->proof->getType());
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testToStringWithNullId(): void
    {
        $this->assertSame('', (string) $this->proof);
    }

    public function testToStringWithId(): void
    {
        $this->proof->setId(99);
        $this->assertSame('99', (string) $this->proof);
    }
}
