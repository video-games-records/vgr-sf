<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Message\Unit\Domain\ValueObject;

use App\BoundedContext\Message\Domain\ValueObject\MessageTypeEnum;
use PHPUnit\Framework\TestCase;

class MessageTypeEnumTest extends TestCase
{
    public function testGetLabelForAllCases(): void
    {
        $this->assertSame('Default', MessageTypeEnum::DEFAULT->getLabel());
        $this->assertSame('Admin Notification', MessageTypeEnum::ADMIN_NOTIF->getLabel());
        $this->assertSame('Forum Notification', MessageTypeEnum::FORUM_NOTIF->getLabel());
        $this->assertSame('Player Badge', MessageTypeEnum::VGR_PLAYER_BADGE->getLabel());
        $this->assertSame('Team Badge', MessageTypeEnum::VGR_TEAM_BADGE->getLabel());
        $this->assertSame('Proof Request Accepted', MessageTypeEnum::VGR_PROOF_REQUEST_ACCEPTED->getLabel());
        $this->assertSame('Proof Accepted', MessageTypeEnum::VGR_PROOF_ACCEPTED->getLabel());
        $this->assertSame('Proof Refused', MessageTypeEnum::VGR_PROOF_REFUSED->getLabel());
        $this->assertSame('Proof Request Refused', MessageTypeEnum::VGR_PROOF_REQUEST_REFUSED->getLabel());
    }

    public function testIsReplyableReturnsTrueOnlyForDefault(): void
    {
        $this->assertTrue(MessageTypeEnum::DEFAULT->isReplyable());
    }

    public function testIsReplyableReturnsFalseForNonDefaultTypes(): void
    {
        $this->assertFalse(MessageTypeEnum::ADMIN_NOTIF->isReplyable());
        $this->assertFalse(MessageTypeEnum::FORUM_NOTIF->isReplyable());
        $this->assertFalse(MessageTypeEnum::VGR_PLAYER_BADGE->isReplyable());
        $this->assertFalse(MessageTypeEnum::VGR_TEAM_BADGE->isReplyable());
        $this->assertFalse(MessageTypeEnum::VGR_PROOF_REQUEST_ACCEPTED->isReplyable());
        $this->assertFalse(MessageTypeEnum::VGR_PROOF_ACCEPTED->isReplyable());
        $this->assertFalse(MessageTypeEnum::VGR_PROOF_REFUSED->isReplyable());
        $this->assertFalse(MessageTypeEnum::VGR_PROOF_REQUEST_REFUSED->isReplyable());
    }

    public function testCasesCountIsNine(): void
    {
        $this->assertCount(9, MessageTypeEnum::cases());
    }
}
