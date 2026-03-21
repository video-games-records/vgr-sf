<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Message\Unit\Entity;

use App\BoundedContext\Message\Domain\Entity\Message;
use App\BoundedContext\Message\Domain\ValueObject\MessageTypeEnum;
use App\BoundedContext\User\Domain\Entity\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    private Message $message;

    protected function setUp(): void
    {
        $this->message = new Message();
    }

    // ------------------------------------------------------------------
    // Default values
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->message->getId());
    }

    public function testTypeDefaultsToDefault(): void
    {
        $this->assertSame('DEFAULT', $this->message->getType());
    }

    public function testMessageDefaultsToNull(): void
    {
        $this->assertNull($this->message->getMessage());
    }

    public function testIsOpenedDefaultsToFalse(): void
    {
        $this->assertFalse($this->message->getIsOpened());
    }

    public function testIsDeletedSenderDefaultsToFalse(): void
    {
        $this->assertFalse($this->message->getIsDeletedSender());
    }

    public function testIsDeletedRecipientDefaultsToFalse(): void
    {
        $this->assertFalse($this->message->getIsDeletedRecipient());
    }

    // ------------------------------------------------------------------
    // Id getter / setter
    // ------------------------------------------------------------------

    public function testSetAndGetId(): void
    {
        $result = $this->message->setId(42);
        $this->assertSame(42, $this->message->getId());
        $this->assertSame($this->message, $result);
    }

    // ------------------------------------------------------------------
    // Object getter / setter
    // ------------------------------------------------------------------

    public function testSetAndGetObject(): void
    {
        $result = $this->message->setObject('Hello there');
        $this->assertSame('Hello there', $this->message->getObject());
        $this->assertSame($this->message, $result);
    }

    // ------------------------------------------------------------------
    // Message body getter / setter
    // ------------------------------------------------------------------

    public function testSetAndGetMessage(): void
    {
        $result = $this->message->setMessage('Message body content.');
        $this->assertSame('Message body content.', $this->message->getMessage());
        $this->assertSame($this->message, $result);
    }

    // ------------------------------------------------------------------
    // Type getter / setter
    // ------------------------------------------------------------------

    public function testSetTypeWithString(): void
    {
        $result = $this->message->setType('ADMIN_NOTIF');
        $this->assertSame('ADMIN_NOTIF', $this->message->getType());
        $this->assertSame($this->message, $result);
    }

    public function testSetTypeWithEnum(): void
    {
        $result = $this->message->setType(MessageTypeEnum::FORUM_NOTIF);
        $this->assertSame('FORUM_NOTIF', $this->message->getType());
        $this->assertSame($this->message, $result);
    }

    public function testGetTypeEnumReturnsCorrectEnum(): void
    {
        $this->message->setType(MessageTypeEnum::VGR_PLAYER_BADGE);
        $this->assertSame(MessageTypeEnum::VGR_PLAYER_BADGE, $this->message->getTypeEnum());
    }

    public function testGetTypeEnumFallsBackToDefaultForUnknownType(): void
    {
        $this->message->setType('UNKNOWN_TYPE');
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

    #[DataProvider('nonReplyableTypesProvider')]
    public function testIsReplyableReturnsFalseForNonReplyableTypes(MessageTypeEnum $type): void
    {
        $this->message->setType($type);
        $this->assertFalse($this->message->isReplyable());
    }

    /**
     * @return array<string, array{MessageTypeEnum}>
     */
    public static function nonReplyableTypesProvider(): array
    {
        return [
            'ADMIN_NOTIF' => [MessageTypeEnum::ADMIN_NOTIF],
            'FORUM_NOTIF' => [MessageTypeEnum::FORUM_NOTIF],
            'VGR_PLAYER_BADGE' => [MessageTypeEnum::VGR_PLAYER_BADGE],
            'VGR_TEAM_BADGE' => [MessageTypeEnum::VGR_TEAM_BADGE],
            'VGR_PROOF_REQUEST_ACCEPTED' => [MessageTypeEnum::VGR_PROOF_REQUEST_ACCEPTED],
            'VGR_PROOF_ACCEPTED' => [MessageTypeEnum::VGR_PROOF_ACCEPTED],
            'VGR_PROOF_REFUSED' => [MessageTypeEnum::VGR_PROOF_REFUSED],
            'VGR_PROOF_REQUEST_REFUSED' => [MessageTypeEnum::VGR_PROOF_REQUEST_REFUSED],
        ];
    }

    // ------------------------------------------------------------------
    // Sender relation
    // ------------------------------------------------------------------

    public function testSetAndGetSender(): void
    {
        $user = $this->createMock(User::class);
        $result = $this->message->setSender($user);
        $this->assertSame($user, $this->message->getSender());
        $this->assertSame($this->message, $result);
    }

    // ------------------------------------------------------------------
    // Recipient relation
    // ------------------------------------------------------------------

    public function testSetAndGetRecipient(): void
    {
        $user = $this->createMock(User::class);
        $result = $this->message->setRecipient($user);
        $this->assertSame($user, $this->message->getRecipient());
        $this->assertSame($this->message, $result);
    }

    // ------------------------------------------------------------------
    // isOpened getter / setter
    // ------------------------------------------------------------------

    public function testSetAndGetIsOpened(): void
    {
        $result = $this->message->setIsOpened(true);
        $this->assertTrue($this->message->getIsOpened());
        $this->assertSame($this->message, $result);
    }

    public function testSetIsOpenedToFalse(): void
    {
        $this->message->setIsOpened(true);
        $this->message->setIsOpened(false);
        $this->assertFalse($this->message->getIsOpened());
    }

    // ------------------------------------------------------------------
    // isDeletedSender getter / setter
    // ------------------------------------------------------------------

    public function testSetAndGetIsDeletedSender(): void
    {
        $result = $this->message->setIsDeletedSender(true);
        $this->assertTrue($this->message->getIsDeletedSender());
        $this->assertSame($this->message, $result);
    }

    public function testSetIsDeletedSenderToFalse(): void
    {
        $this->message->setIsDeletedSender(true);
        $this->message->setIsDeletedSender(false);
        $this->assertFalse($this->message->getIsDeletedSender());
    }

    // ------------------------------------------------------------------
    // isDeletedRecipient getter / setter
    // ------------------------------------------------------------------

    public function testSetAndGetIsDeletedRecipient(): void
    {
        $result = $this->message->setIsDeletedRecipient(true);
        $this->assertTrue($this->message->getIsDeletedRecipient());
        $this->assertSame($this->message, $result);
    }

    public function testSetIsDeletedRecipientToFalse(): void
    {
        $this->message->setIsDeletedRecipient(true);
        $this->message->setIsDeletedRecipient(false);
        $this->assertFalse($this->message->getIsDeletedRecipient());
    }

    // ------------------------------------------------------------------
    // __toString
    // ------------------------------------------------------------------

    public function testToStringContainsMessage(): void
    {
        $result = (string) $this->message;
        $this->assertStringContainsString('Message', $result);
    }

    public function testToStringWithIdSet(): void
    {
        $this->message->setId(7);
        $this->assertSame('Message [7]', (string) $this->message);
    }
}
