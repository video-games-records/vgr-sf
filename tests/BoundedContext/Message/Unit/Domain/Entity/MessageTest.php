<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Message\Unit\Domain\Entity;

use App\BoundedContext\Message\Domain\Entity\Message;
use App\BoundedContext\Message\Domain\ValueObject\MessageTypeEnum;
use App\BoundedContext\User\Domain\Entity\User;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    private Message $message;

    protected function setUp(): void
    {
        $this->message = new Message();
    }

    // ------------------------------------------------------------------
    // id
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->message->getId());
    }

    public function testSetAndGetId(): void
    {
        $this->message->setId(42);
        $this->assertSame(42, $this->message->getId());
    }

    public function testSetIdReturnsSelf(): void
    {
        $result = $this->message->setId(1);
        $this->assertSame($this->message, $result);
    }

    // ------------------------------------------------------------------
    // object
    // ------------------------------------------------------------------

    public function testSetAndGetObject(): void
    {
        $this->message->setObject('Hello subject');
        $this->assertSame('Hello subject', $this->message->getObject());
    }

    public function testSetObjectReturnsSelf(): void
    {
        $result = $this->message->setObject('subject');
        $this->assertSame($this->message, $result);
    }

    // ------------------------------------------------------------------
    // message body
    // ------------------------------------------------------------------

    public function testMessageDefaultsToNull(): void
    {
        $this->assertNull($this->message->getMessage());
    }

    public function testSetAndGetMessage(): void
    {
        $this->message->setMessage('Hello body');
        $this->assertSame('Hello body', $this->message->getMessage());
    }

    public function testSetMessageReturnsSelf(): void
    {
        $result = $this->message->setMessage('body');
        $this->assertSame($this->message, $result);
    }

    // ------------------------------------------------------------------
    // type
    // ------------------------------------------------------------------

    public function testTypeDefaultsToDefault(): void
    {
        $this->assertSame('DEFAULT', $this->message->getType());
    }

    public function testSetTypeWithString(): void
    {
        $this->message->setType('ADMIN_NOTIF');
        $this->assertSame('ADMIN_NOTIF', $this->message->getType());
    }

    public function testSetTypeWithEnum(): void
    {
        $this->message->setType(MessageTypeEnum::FORUM_NOTIF);
        $this->assertSame('FORUM_NOTIF', $this->message->getType());
    }

    public function testSetTypeReturnsSelf(): void
    {
        $result = $this->message->setType('DEFAULT');
        $this->assertSame($this->message, $result);
    }

    // ------------------------------------------------------------------
    // getTypeEnum
    // ------------------------------------------------------------------

    public function testGetTypeEnumReturnsDefaultEnum(): void
    {
        $this->assertSame(MessageTypeEnum::DEFAULT, $this->message->getTypeEnum());
    }

    public function testGetTypeEnumReturnsCorrectEnum(): void
    {
        $this->message->setType('VGR_PLAYER_BADGE');
        $this->assertSame(MessageTypeEnum::VGR_PLAYER_BADGE, $this->message->getTypeEnum());
    }

    public function testGetTypeEnumFallsBackToDefaultForUnknownValue(): void
    {
        // Force an invalid type via reflection
        $ref = new \ReflectionProperty(Message::class, 'type');
        $ref->setValue($this->message, 'UNKNOWN_TYPE');

        $this->assertSame(MessageTypeEnum::DEFAULT, $this->message->getTypeEnum());
    }

    // ------------------------------------------------------------------
    // isReplyable
    // ------------------------------------------------------------------

    public function testIsReplyableReturnsTrueForDefaultType(): void
    {
        $this->message->setType(MessageTypeEnum::DEFAULT);
        $this->assertTrue($this->message->isReplyable());
    }

    public function testIsReplyableReturnsFalseForAdminNotif(): void
    {
        $this->message->setType(MessageTypeEnum::ADMIN_NOTIF);
        $this->assertFalse($this->message->isReplyable());
    }

    public function testIsReplyableReturnsFalseForForumNotif(): void
    {
        $this->message->setType(MessageTypeEnum::FORUM_NOTIF);
        $this->assertFalse($this->message->isReplyable());
    }

    public function testIsReplyableReturnsFalseForPlayerBadge(): void
    {
        $this->message->setType(MessageTypeEnum::VGR_PLAYER_BADGE);
        $this->assertFalse($this->message->isReplyable());
    }

    public function testIsReplyableReturnsFalseForTeamBadge(): void
    {
        $this->message->setType(MessageTypeEnum::VGR_TEAM_BADGE);
        $this->assertFalse($this->message->isReplyable());
    }

    public function testIsReplyableReturnsFalseForProofRequestAccepted(): void
    {
        $this->message->setType(MessageTypeEnum::VGR_PROOF_REQUEST_ACCEPTED);
        $this->assertFalse($this->message->isReplyable());
    }

    public function testIsReplyableReturnsFalseForProofAccepted(): void
    {
        $this->message->setType(MessageTypeEnum::VGR_PROOF_ACCEPTED);
        $this->assertFalse($this->message->isReplyable());
    }

    public function testIsReplyableReturnsFalseForProofRefused(): void
    {
        $this->message->setType(MessageTypeEnum::VGR_PROOF_REFUSED);
        $this->assertFalse($this->message->isReplyable());
    }

    public function testIsReplyableReturnsFalseForProofRequestRefused(): void
    {
        $this->message->setType(MessageTypeEnum::VGR_PROOF_REQUEST_REFUSED);
        $this->assertFalse($this->message->isReplyable());
    }

    // ------------------------------------------------------------------
    // sender
    // ------------------------------------------------------------------

    public function testSetAndGetSender(): void
    {
        $user = $this->createMock(User::class);
        $this->message->setSender($user);
        $this->assertSame($user, $this->message->getSender());
    }

    public function testSetSenderReturnsSelf(): void
    {
        $user = $this->createMock(User::class);
        $result = $this->message->setSender($user);
        $this->assertSame($this->message, $result);
    }

    // ------------------------------------------------------------------
    // recipient
    // ------------------------------------------------------------------

    public function testSetAndGetRecipient(): void
    {
        $user = $this->createMock(User::class);
        $this->message->setRecipient($user);
        $this->assertSame($user, $this->message->getRecipient());
    }

    public function testSetRecipientReturnsSelf(): void
    {
        $user = $this->createMock(User::class);
        $result = $this->message->setRecipient($user);
        $this->assertSame($this->message, $result);
    }

    // ------------------------------------------------------------------
    // isOpened
    // ------------------------------------------------------------------

    public function testIsOpenedDefaultsToFalse(): void
    {
        $this->assertFalse($this->message->getIsOpened());
    }

    public function testSetIsOpenedToTrue(): void
    {
        $this->message->setIsOpened(true);
        $this->assertTrue($this->message->getIsOpened());
    }

    public function testSetIsOpenedToFalse(): void
    {
        $this->message->setIsOpened(true);
        $this->message->setIsOpened(false);
        $this->assertFalse($this->message->getIsOpened());
    }

    public function testSetIsOpenedReturnsSelf(): void
    {
        $result = $this->message->setIsOpened(true);
        $this->assertSame($this->message, $result);
    }

    // ------------------------------------------------------------------
    // isDeletedSender
    // ------------------------------------------------------------------

    public function testIsDeletedSenderDefaultsToFalse(): void
    {
        $this->assertFalse($this->message->getIsDeletedSender());
    }

    public function testSetIsDeletedSenderToTrue(): void
    {
        $this->message->setIsDeletedSender(true);
        $this->assertTrue($this->message->getIsDeletedSender());
    }

    public function testSetIsDeletedSenderReturnsSelf(): void
    {
        $result = $this->message->setIsDeletedSender(true);
        $this->assertSame($this->message, $result);
    }

    // ------------------------------------------------------------------
    // isDeletedRecipient
    // ------------------------------------------------------------------

    public function testIsDeletedRecipientDefaultsToFalse(): void
    {
        $this->assertFalse($this->message->getIsDeletedRecipient());
    }

    public function testSetIsDeletedRecipientToTrue(): void
    {
        $this->message->setIsDeletedRecipient(true);
        $this->assertTrue($this->message->getIsDeletedRecipient());
    }

    public function testSetIsDeletedRecipientReturnsSelf(): void
    {
        $result = $this->message->setIsDeletedRecipient(true);
        $this->assertSame($this->message, $result);
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToStringContainsMessageWord(): void
    {
        $this->assertStringContainsString('Message', (string) $this->message);
    }

    public function testToStringContainsBrackets(): void
    {
        $this->assertStringContainsString('[', (string) $this->message);
        $this->assertStringContainsString(']', (string) $this->message);
    }

    public function testToStringContainsIdWhenSet(): void
    {
        $this->message->setId(99);
        $this->assertStringContainsString('99', (string) $this->message);
    }
}
