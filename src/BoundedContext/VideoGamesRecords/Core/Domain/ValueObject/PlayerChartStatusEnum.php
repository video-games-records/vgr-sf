<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject;

enum PlayerChartStatusEnum: string
{
    case NONE = 'none';
    case REQUEST_PENDING = 'request-pending';
    case REQUEST_VALIDATED = 'request-validated';
    case REQUEST_PROOF_SENT = 'request-proof-sent';
    case PROOF_SENT = 'proof-sent';
    case PROVED = 'proved';
    case UNPROVED = 'unproved';

    public function getLabel(): string
    {
        return match ($this) {
            self::NONE => 'None',
            self::REQUEST_PENDING => 'Request Pending',
            self::REQUEST_VALIDATED => 'Request Validated',
            self::REQUEST_PROOF_SENT => 'Request Proof Sent',
            self::PROOF_SENT => 'Proof Sent',
            self::PROVED => 'Proved',
            self::UNPROVED => 'Unproved',
        };
    }

    public function getCssClass(): string
    {
        return $this->value;
    }

    public function allowsRanking(): bool
    {
        return match ($this) {
            self::UNPROVED => false,
            default => true,
        };
    }

    public function requiresProof(): bool
    {
        return match ($this) {
            self::REQUEST_PROOF_SENT, self::PROOF_SENT => true,
            default => false,
        };
    }


    /**
     * Get statuses that allow proving
     */
    /**
     * @return array<self>
     */
    public static function getStatusForProving(): array
    {
        return [
            self::NONE,
            self::REQUEST_VALIDATED,
            self::UNPROVED,
        ];
    }
}
