<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Domain\ValueObject;

use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PlayerChartStatusEnumTest extends TestCase
{
    // ------------------------------------------------------------------
    // getLabel
    // ------------------------------------------------------------------

    #[DataProvider('labelProvider')]
    public function testGetLabel(PlayerChartStatusEnum $status, string $expected): void
    {
        $this->assertSame($expected, $status->getLabel());
    }

    /**
     * @return array<string, array{PlayerChartStatusEnum, string}>
     */
    public static function labelProvider(): array
    {
        return [
            'NONE'                  => [PlayerChartStatusEnum::NONE, 'None'],
            'REQUEST_PENDING'       => [PlayerChartStatusEnum::REQUEST_PENDING, 'Request Pending'],
            'REQUEST_VALIDATED'     => [PlayerChartStatusEnum::REQUEST_VALIDATED, 'Request Validated'],
            'REQUEST_PROOF_SENT'    => [PlayerChartStatusEnum::REQUEST_PROOF_SENT, 'Request Proof Sent'],
            'PROOF_SENT'            => [PlayerChartStatusEnum::PROOF_SENT, 'Proof Sent'],
            'PROVED'                => [PlayerChartStatusEnum::PROVED, 'Proved'],
            'UNPROVED'              => [PlayerChartStatusEnum::UNPROVED, 'Unproved'],
        ];
    }

    // ------------------------------------------------------------------
    // getCssClass
    // ------------------------------------------------------------------

    public function testGetCssClassReturnsEnumValue(): void
    {
        foreach (PlayerChartStatusEnum::cases() as $case) {
            $this->assertSame($case->value, $case->getCssClass());
        }
    }

    // ------------------------------------------------------------------
    // allowsRanking
    // ------------------------------------------------------------------

    public function testAllowsRankingReturnsFalseOnlyForUnproved(): void
    {
        $this->assertFalse(PlayerChartStatusEnum::UNPROVED->allowsRanking());

        foreach (PlayerChartStatusEnum::cases() as $case) {
            if ($case !== PlayerChartStatusEnum::UNPROVED) {
                $this->assertTrue($case->allowsRanking(), "Expected {$case->name} to allow ranking");
            }
        }
    }

    // ------------------------------------------------------------------
    // requiresProof
    // ------------------------------------------------------------------

    public function testRequiresProofReturnsTrueForProofStatuses(): void
    {
        $this->assertTrue(PlayerChartStatusEnum::REQUEST_PROOF_SENT->requiresProof());
        $this->assertTrue(PlayerChartStatusEnum::PROOF_SENT->requiresProof());
    }

    public function testRequiresProofReturnsFalseForOtherStatuses(): void
    {
        $proofStatuses = [PlayerChartStatusEnum::REQUEST_PROOF_SENT, PlayerChartStatusEnum::PROOF_SENT];
        foreach (PlayerChartStatusEnum::cases() as $case) {
            if (!in_array($case, $proofStatuses, true)) {
                $this->assertFalse($case->requiresProof(), "Expected {$case->name} to not require proof");
            }
        }
    }

    // ------------------------------------------------------------------
    // getStatusForProving
    // ------------------------------------------------------------------

    public function testGetStatusForProvingContainsThreeStatuses(): void
    {
        $statuses = PlayerChartStatusEnum::getStatusForProving();
        $this->assertCount(3, $statuses);
    }

    public function testGetStatusForProvingContainsExpectedStatuses(): void
    {
        $statuses = PlayerChartStatusEnum::getStatusForProving();
        $this->assertContains(PlayerChartStatusEnum::NONE, $statuses);
        $this->assertContains(PlayerChartStatusEnum::REQUEST_VALIDATED, $statuses);
        $this->assertContains(PlayerChartStatusEnum::UNPROVED, $statuses);
    }
}
