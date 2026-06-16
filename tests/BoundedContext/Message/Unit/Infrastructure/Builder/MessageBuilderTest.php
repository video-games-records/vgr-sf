<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Message\Unit\Infrastructure\Builder;

use App\BoundedContext\Message\Domain\Entity\Message;
use App\BoundedContext\Message\Domain\ValueObject\MessageTypeEnum;
use App\BoundedContext\Message\Infrastructure\Builder\MessageBuilder;
use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class MessageBuilderTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private MessageBuilder $builder;
    private User $sender;
    private User $recipient;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->builder = new MessageBuilder($this->em);

        $this->sender = $this->createMock(User::class);
        $this->recipient = $this->createMock(User::class);
    }

    // ------------------------------------------------------------------
    // Fluent setters return self
    // ------------------------------------------------------------------

    public function testSetTypeReturnsSelf(): void
    {
        $result = $this->builder->setType('DEFAULT');
        $this->assertSame($this->builder, $result);
    }

    public function testSetObjectReturnsSelf(): void
    {
        $result = $this->builder->setObject('Test subject');
        $this->assertSame($this->builder, $result);
    }

    public function testSetMessageReturnsSelf(): void
    {
        $result = $this->builder->setMessage('Test body');
        $this->assertSame($this->builder, $result);
    }

    public function testSetSenderReturnsSelf(): void
    {
        $result = $this->builder->setSender($this->sender);
        $this->assertSame($this->builder, $result);
    }

    public function testSetRecipientReturnsSelf(): void
    {
        $result = $this->builder->setRecipient($this->recipient);
        $this->assertSame($this->builder, $result);
    }

    // ------------------------------------------------------------------
    // setType accepts both string and enum
    // ------------------------------------------------------------------

    public function testSetTypeAcceptsString(): void
    {
        $this->builder
            ->setType('ADMIN_NOTIF')
            ->setObject('subject')
            ->setMessage('body')
            ->setSender($this->sender)
            ->setRecipient($this->recipient);

        $persistedMessage = null;
        $this->em->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function (Message $msg) use (&$persistedMessage) {
                $persistedMessage = $msg;
            });
        $this->em->expects($this->once())->method('flush');

        $this->builder->send();

        $this->assertSame('ADMIN_NOTIF', $persistedMessage->getType());
    }

    public function testSetTypeAcceptsEnum(): void
    {
        $this->builder
            ->setType(MessageTypeEnum::FORUM_NOTIF)
            ->setObject('subject')
            ->setMessage('body')
            ->setSender($this->sender)
            ->setRecipient($this->recipient);

        $persistedMessage = null;
        $this->em->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function (Message $msg) use (&$persistedMessage) {
                $persistedMessage = $msg;
            });
        $this->em->expects($this->once())->method('flush');

        $this->builder->send();

        $this->assertSame('FORUM_NOTIF', $persistedMessage->getType());
    }

    // ------------------------------------------------------------------
    // send — entity construction
    // ------------------------------------------------------------------

    public function testSendPersistsAndFlushes(): void
    {
        $this->builder
            ->setObject('My Subject')
            ->setMessage('My Body')
            ->setSender($this->sender)
            ->setRecipient($this->recipient);

        $this->em->expects($this->once())->method('persist')->with($this->isInstanceOf(Message::class));
        $this->em->expects($this->once())->method('flush');

        $this->builder->send();
    }

    public function testSendSetsCorrectObject(): void
    {
        $this->builder
            ->setObject('The Subject')
            ->setMessage('The Body')
            ->setSender($this->sender)
            ->setRecipient($this->recipient);

        $persistedMessage = null;
        $this->em->method('persist')->willReturnCallback(function (Message $msg) use (&$persistedMessage) {
            $persistedMessage = $msg;
        });

        $this->builder->send();

        $this->assertSame('The Subject', $persistedMessage->getObject());
    }

    public function testSendSetsCorrectMessage(): void
    {
        $this->builder
            ->setObject('Subject')
            ->setMessage('The message body')
            ->setSender($this->sender)
            ->setRecipient($this->recipient);

        $persistedMessage = null;
        $this->em->method('persist')->willReturnCallback(function (Message $msg) use (&$persistedMessage) {
            $persistedMessage = $msg;
        });

        $this->builder->send();

        $this->assertSame('The message body', $persistedMessage->getMessage());
    }

    public function testSendSetsCorrectSender(): void
    {
        $this->builder
            ->setObject('Subject')
            ->setMessage('Body')
            ->setSender($this->sender)
            ->setRecipient($this->recipient);

        $persistedMessage = null;
        $this->em->method('persist')->willReturnCallback(function (Message $msg) use (&$persistedMessage) {
            $persistedMessage = $msg;
        });

        $this->builder->send();

        $this->assertSame($this->sender, $persistedMessage->getSender());
    }

    public function testSendSetsCorrectRecipient(): void
    {
        $this->builder
            ->setObject('Subject')
            ->setMessage('Body')
            ->setSender($this->sender)
            ->setRecipient($this->recipient);

        $persistedMessage = null;
        $this->em->method('persist')->willReturnCallback(function (Message $msg) use (&$persistedMessage) {
            $persistedMessage = $msg;
        });

        $this->builder->send();

        $this->assertSame($this->recipient, $persistedMessage->getRecipient());
    }

    public function testSendMarksIsDeletedSenderAsTrue(): void
    {
        $this->builder
            ->setObject('Subject')
            ->setMessage('Body')
            ->setSender($this->sender)
            ->setRecipient($this->recipient);

        $persistedMessage = null;
        $this->em->method('persist')->willReturnCallback(function (Message $msg) use (&$persistedMessage) {
            $persistedMessage = $msg;
        });

        $this->builder->send();

        $this->assertTrue($persistedMessage->getIsDeletedSender());
    }

    public function testSendDoesNotMarkIsDeletedRecipient(): void
    {
        $this->builder
            ->setObject('Subject')
            ->setMessage('Body')
            ->setSender($this->sender)
            ->setRecipient($this->recipient);

        $persistedMessage = null;
        $this->em->method('persist')->willReturnCallback(function (Message $msg) use (&$persistedMessage) {
            $persistedMessage = $msg;
        });

        $this->builder->send();

        $this->assertFalse($persistedMessage->getIsDeletedRecipient());
    }

    public function testSendUsesDefaultTypeWhenNotSet(): void
    {
        $this->builder
            ->setObject('Subject')
            ->setMessage('Body')
            ->setSender($this->sender)
            ->setRecipient($this->recipient);

        $persistedMessage = null;
        $this->em->method('persist')->willReturnCallback(function (Message $msg) use (&$persistedMessage) {
            $persistedMessage = $msg;
        });

        $this->builder->send();

        $this->assertSame('DEFAULT', $persistedMessage->getType());
    }

    public function testSendMessageIsNotOpenedByDefault(): void
    {
        $this->builder
            ->setObject('Subject')
            ->setMessage('Body')
            ->setSender($this->sender)
            ->setRecipient($this->recipient);

        $persistedMessage = null;
        $this->em->method('persist')->willReturnCallback(function (Message $msg) use (&$persistedMessage) {
            $persistedMessage = $msg;
        });

        $this->builder->send();

        $this->assertFalse($persistedMessage->getIsOpened());
    }
}
