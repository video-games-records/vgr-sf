<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Proof\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\ProofRequest;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\ProofRequestStatus;
use DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProofRequestTest extends TestCase
{
    private ProofRequest $proofRequest;

    protected function setUp(): void
    {
        $this->proofRequest = new ProofRequest();
    }

    // ------------------------------------------------------------------
    // Basic properties — defaults
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->proofRequest->getId());
    }

    public function testStatusDefaultsToInProgress(): void
    {
        $this->assertSame(ProofRequestStatus::IN_PROGRESS, $this->proofRequest->getStatus());
    }

    public function testResponseDefaultsToNull(): void
    {
        $this->assertNull($this->proofRequest->getResponse());
    }

    public function testMessageDefaultsToNull(): void
    {
        $this->assertNull($this->proofRequest->getMessage());
    }

    public function testDateAcceptanceDefaultsToNull(): void
    {
        $this->assertNull($this->proofRequest->getDateAcceptance());
    }

    public function testPlayerRespondingDefaultsToNull(): void
    {
        $this->assertNull($this->proofRequest->getPlayerResponding());
    }

    // ------------------------------------------------------------------
    // Getters / setters
    // ------------------------------------------------------------------

    public function testSetAndGetId(): void
    {
        $result = $this->proofRequest->setId(10);
        $this->assertSame(10, $this->proofRequest->getId());
        $this->assertSame($this->proofRequest, $result);
    }

    #[DataProvider('statusValuesProvider')]
    public function testSetAndGetStatus(string $status): void
    {
        $result = $this->proofRequest->setStatus($status);
        $this->assertSame($status, $this->proofRequest->getStatus());
        $this->assertSame($this->proofRequest, $result);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function statusValuesProvider(): array
    {
        return [
            'IN_PROGRESS' => [ProofRequestStatus::IN_PROGRESS],
            'REFUSED'     => [ProofRequestStatus::REFUSED],
            'ACCEPTED'    => [ProofRequestStatus::ACCEPTED],
        ];
    }

    public function testSetAndGetResponse(): void
    {
        $result = $this->proofRequest->setResponse('Your proof is valid.');
        $this->assertSame('Your proof is valid.', $this->proofRequest->getResponse());
        $this->assertSame($this->proofRequest, $result);
    }

    public function testSetAndGetMessage(): void
    {
        $result = $this->proofRequest->setMessage('Please provide video evidence.');
        $this->assertSame('Please provide video evidence.', $this->proofRequest->getMessage());
        $this->assertSame($this->proofRequest, $result);
    }

    public function testSetAndGetDateAcceptance(): void
    {
        $date = new DateTime('2025-06-01 12:00:00');
        $result = $this->proofRequest->setDateAcceptance($date);
        $this->assertSame($date, $this->proofRequest->getDateAcceptance());
        $this->assertSame($this->proofRequest, $result);
    }

    // ------------------------------------------------------------------
    // PlayerChart relation
    // ------------------------------------------------------------------

    public function testSetAndGetPlayerChart(): void
    {
        $playerChart = $this->createMock(PlayerChart::class);
        $result = $this->proofRequest->setPlayerChart($playerChart);
        $this->assertSame($playerChart, $this->proofRequest->getPlayerChart());
        $this->assertSame($this->proofRequest, $result);
    }

    // ------------------------------------------------------------------
    // Player relations
    // ------------------------------------------------------------------

    public function testSetAndGetPlayerRequesting(): void
    {
        $player = $this->createMock(Player::class);
        $result = $this->proofRequest->setPlayerRequesting($player);
        $this->assertSame($player, $this->proofRequest->getPlayerRequesting());
        $this->assertSame($this->proofRequest, $result);
    }

    public function testSetAndGetPlayerResponding(): void
    {
        $player = $this->createMock(Player::class);
        $result = $this->proofRequest->setPlayerResponding($player);
        $this->assertSame($player, $this->proofRequest->getPlayerResponding());
        $this->assertSame($this->proofRequest, $result);
    }

    public function testSetPlayerRespondingToNull(): void
    {
        $player = $this->createMock(Player::class);
        $this->proofRequest->setPlayerResponding($player);
        $this->proofRequest->setPlayerResponding(null);
        $this->assertNull($this->proofRequest->getPlayerResponding());
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testToStringWithNullId(): void
    {
        $this->assertSame('Request []', (string) $this->proofRequest);
    }

    public function testToStringWithId(): void
    {
        $this->proofRequest->setId(55);
        $this->assertSame('Request [55]', (string) $this->proofRequest);
    }
}
