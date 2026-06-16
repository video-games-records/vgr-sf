<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Message\Unit\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\Message\Domain\Entity\Message;
use App\BoundedContext\Message\Infrastructure\Doctrine\EventListener\MessageListener;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

#[AllowMockObjectsWithoutExpectations]
class MessageListenerTest extends TestCase
{
    private HtmlSanitizerInterface&MockObject $sanitizer;
    private MessageListener $listener;

    protected function setUp(): void
    {
        $this->sanitizer = $this->createMock(HtmlSanitizerInterface::class);
        $this->listener = new MessageListener($this->sanitizer);
    }

    // ------------------------------------------------------------------
    // prePersist
    // ------------------------------------------------------------------

    public function testPrePersistSanitizesMessageBody(): void
    {
        $message = new Message();
        $message->setMessage('<script>alert(1)</script>Hello');

        $this->sanitizer->expects($this->once())
            ->method('sanitize')
            ->with('<script>alert(1)</script>Hello')
            ->willReturn('Hello');

        $this->listener->prePersist($message);

        $this->assertSame('Hello', $message->getMessage());
    }

    public function testPrePersistDoesNotCallSanitizerWhenMessageIsNull(): void
    {
        $message = new Message();
        // message body is null by default

        $this->sanitizer->expects($this->never())->method('sanitize');

        $this->listener->prePersist($message);
    }

    public function testPrePersistDoesNotCallSanitizerWhenMessageIsEmptyString(): void
    {
        $message = new Message();
        // empty string is falsy: purifyMessage skips it
        $message->setMessage('');

        $this->sanitizer->expects($this->never())->method('sanitize');

        $this->listener->prePersist($message);
    }

    public function testPrePersistPreservesCleanContent(): void
    {
        $message = new Message();
        $message->setMessage('<p>Clean content</p>');

        $this->sanitizer->method('sanitize')->willReturn('<p>Clean content</p>');

        $this->listener->prePersist($message);

        $this->assertSame('<p>Clean content</p>', $message->getMessage());
    }

    // ------------------------------------------------------------------
    // preUpdate
    // ------------------------------------------------------------------

    public function testPreUpdateSanitizesMessageBody(): void
    {
        $message = new Message();
        $message->setMessage('<img src=x onerror=alert(1)>Text');

        $this->sanitizer->expects($this->once())
            ->method('sanitize')
            ->with('<img src=x onerror=alert(1)>Text')
            ->willReturn('Text');

        $this->listener->preUpdate($message);

        $this->assertSame('Text', $message->getMessage());
    }

    public function testPreUpdateDoesNotCallSanitizerWhenMessageIsNull(): void
    {
        $message = new Message();

        $this->sanitizer->expects($this->never())->method('sanitize');

        $this->listener->preUpdate($message);
    }

    public function testPreUpdateDoesNotCallSanitizerWhenMessageIsEmptyString(): void
    {
        $message = new Message();
        $message->setMessage('');

        $this->sanitizer->expects($this->never())->method('sanitize');

        $this->listener->preUpdate($message);
    }

    public function testPreUpdatePreservesCleanContent(): void
    {
        $message = new Message();
        $message->setMessage('<p>Updated content</p>');

        $this->sanitizer->method('sanitize')->willReturn('<p>Updated content</p>');

        $this->listener->preUpdate($message);

        $this->assertSame('<p>Updated content</p>', $message->getMessage());
    }

    // ------------------------------------------------------------------
    // Both events call same sanitization logic
    // ------------------------------------------------------------------

    public function testPrePersistAndPreUpdateBothSanitize(): void
    {
        $messagePersist = new Message();
        $messagePersist->setMessage('<b>Bold</b>');

        $messageUpdate = new Message();
        $messageUpdate->setMessage('<i>Italic</i>');

        $this->sanitizer->expects($this->exactly(2))
            ->method('sanitize')
            ->willReturnOnConsecutiveCalls('<b>Bold</b>', '<i>Italic</i>');

        $this->listener->prePersist($messagePersist);
        $this->listener->preUpdate($messageUpdate);
    }
}
