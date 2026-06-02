<?php

declare(strict_types=1);

namespace App\Tests\SharedKernel\Unit\Infrastructure\Messenger\Middleware;

use App\SharedKernel\Infrastructure\Messenger\Middleware\UniquenessMiddleware;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;
use Symfony\Component\Messenger\Stamp\SentStamp;

#[AllowMockObjectsWithoutExpectations]
class UniquenessMiddlewareTest extends TestCase
{
    private CacheItemPoolInterface&MockObject $cache;
    private UniquenessMiddleware $middleware;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheItemPoolInterface::class);
        $this->middleware = new UniquenessMiddleware($this->cache, 3600);
    }

    private function makeStack(Envelope $result): StackInterface&MockObject
    {
        $next = $this->createMock(MiddlewareInterface::class);
        $next->method('handle')->willReturn($result);

        $stack = $this->createMock(StackInterface::class);
        $stack->method('next')->willReturn($next);

        return $stack;
    }

    private function makeCacheItem(bool $isHit): CacheItemInterface&MockObject
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->method('isHit')->willReturn($isHit);
        return $item;
    }

    public function testNewMessageIsPassedToNextMiddleware(): void
    {
        $envelope = new Envelope(new \stdClass());
        $item = $this->makeCacheItem(false);

        $this->cache->method('getItem')->willReturn($item);
        $this->cache->expects($this->once())->method('save')->with($item);

        $stack = $this->makeStack($envelope);

        $result = $this->middleware->handle($envelope, $stack);

        $this->assertSame($envelope, $result);
    }

    public function testDuplicateMessageIsBlockedByCache(): void
    {
        $envelope = new Envelope(new \stdClass());
        $item = $this->makeCacheItem(true);

        $this->cache->method('getItem')->willReturn($item);
        $this->cache->expects($this->never())->method('save');

        $stack = $this->createMock(StackInterface::class);
        $stack->expects($this->never())->method('next');

        $result = $this->middleware->handle($envelope, $stack);

        $this->assertSame($envelope, $result);
    }

    public function testMessageWithSentStampSkipsUniquenessCheck(): void
    {
        $envelope = new Envelope(new \stdClass(), [new SentStamp('transport', 'alias')]);

        $this->cache->expects($this->never())->method('getItem');

        $stack = $this->makeStack($envelope);

        $result = $this->middleware->handle($envelope, $stack);

        $this->assertSame($envelope, $result);
    }

    public function testMessageWithConsumedByWorkerStampSkipsUniquenessCheck(): void
    {
        $envelope = new Envelope(new \stdClass(), [new ConsumedByWorkerStamp()]);

        $this->cache->expects($this->never())->method('getItem');

        $stack = $this->makeStack($envelope);

        $this->middleware->handle($envelope, $stack);
    }

    public function testMessageHashUsesEntityClassAndId(): void
    {
        $message = new class
        {
            public function getEntityClass(): string
            {
                return 'App\Foo';
            }
            public function getEntityId(): int
            {
                return 42;
            }
        };

        $expectedKey = 'messenger_message_' . md5('App\Foo_42');

        $envelope = new Envelope($message);
        $item = $this->makeCacheItem(false);

        $this->cache->expects($this->once())->method('getItem')->with($expectedKey)->willReturn($item);
        $this->cache->method('save');

        $stack = $this->makeStack($envelope);

        $this->middleware->handle($envelope, $stack);
    }

    public function testMessageHashUsesUniqueIdentifierWhenAvailable(): void
    {
        $message = new class
        {
            public function getUniqueIdentifier(): string
            {
                return 'my-unique-id';
            }
        };

        $expectedKey = 'messenger_message_' . md5('my-unique-id');

        $envelope = new Envelope($message);
        $item = $this->makeCacheItem(false);

        $this->cache->expects($this->once())->method('getItem')->with($expectedKey)->willReturn($item);
        $this->cache->method('save');

        $stack = $this->makeStack($envelope);

        $this->middleware->handle($envelope, $stack);
    }

    public function testCacheIsDeletedAfterConsumedByWorkerSuccess(): void
    {
        $envelope = (new Envelope(new \stdClass()))->with(new ConsumedByWorkerStamp());

        $this->cache->expects($this->never())->method('getItem');

        $stack = $this->makeStack($envelope);

        $this->cache->expects($this->once())->method('deleteItem');

        $this->middleware->handle($envelope, $stack);
    }

    public function testExceptionDuringHandleIsRethrown(): void
    {
        $envelope = new Envelope(new \stdClass());
        $item = $this->makeCacheItem(false);

        $this->cache->method('getItem')->willReturn($item);
        $this->cache->method('save');

        $next = $this->createMock(MiddlewareInterface::class);
        $next->method('handle')->willThrowException(new \RuntimeException('transport error'));

        $stack = $this->createMock(StackInterface::class);
        $stack->method('next')->willReturn($next);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('transport error');

        $this->middleware->handle($envelope, $stack);
    }
}
