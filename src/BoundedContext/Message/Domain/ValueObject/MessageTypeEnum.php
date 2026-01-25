<?php

declare(strict_types=1);

namespace App\BoundedContext\Message\Domain\ValueObject;

enum MessageTypeEnum: string
{
    case DEFAULT = 'DEFAULT';
    case ADMIN_NOTIF = 'ADMIN_NOTIF';
    case FORUM_NOTIF = 'FORUM_NOTIF';
    case VGR_PLAYER_BADGE = 'VGR_PLAYER_BADGE';
    case VGR_TEAM_BADGE = 'VGR_TEAM_BADGE';
    case VGR_PROOF_REQUEST_ACCEPTED = 'VGR_PROOF_REQUEST_ACCEPTED';
    case VGR_PROOF_ACCEPTED = 'VGR_PROOF_ACCEPTED';
    case VGR_PROOF_REFUSED = 'VGR_PROOF_REFUSED';
    case VGR_PROOF_REQUEST_REFUSED = 'VGR_PROOF_REQUEST_REFUSED';

    public function getLabel(): string
    {
        return match ($this) {
            self::DEFAULT => 'Default',
            self::ADMIN_NOTIF => 'Admin Notification',
            self::FORUM_NOTIF => 'Forum Notification',
            self::VGR_PLAYER_BADGE => 'Player Badge',
            self::VGR_TEAM_BADGE => 'Team Badge',
            self::VGR_PROOF_REQUEST_ACCEPTED => 'Proof Request Accepted',
            self::VGR_PROOF_ACCEPTED => 'Proof Accepted',
            self::VGR_PROOF_REFUSED => 'Proof Refused',
            self::VGR_PROOF_REQUEST_REFUSED => 'Proof Request Refused',
        };
    }

    public function isReplyable(): bool
    {
        return $this === self::DEFAULT;
    }
}
