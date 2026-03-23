<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Article\Unit\Application\Service;

use App\BoundedContext\Article\Application\Service\ViewCounterService;
use App\BoundedContext\Article\Domain\Entity\Article;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[AllowMockObjectsWithoutExpectations]
class ViewCounterServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private RequestStack&MockObject $requestStack;
    private CacheItemPoolInterface&MockObject $cache;
    private ViewCounterService $service;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->cache = $this->createMock(CacheItemPoolInterface::class);

        $this->service = new ViewCounterService(
            $this->entityManager,
            $this->requestStack,
            $this->cache,
        );
    }

    public function testIncrementViewDoesNothingWhenNoRequest(): void
    {
        $this->requestStack->method('getCurrentRequest')->willReturn(null);
        $this->cache->expects($this->never())->method('hasItem');

        $article = new Article();
        $this->service->incrementView($article);
    }

    public function testIncrementViewDoesNothingWhenNoIp(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getClientIp')->willReturn(null);
        $this->requestStack->method('getCurrentRequest')->willReturn($request);
        $this->cache->expects($this->never())->method('hasItem');

        $article = new Article();
        $this->service->incrementView($article);
    }

    public function testIncrementViewDoesNotIncrementIfAlreadyViewed(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getClientIp')->willReturn('127.0.0.1');
        $this->requestStack->method('getCurrentRequest')->willReturn($request);
        $this->cache->method('hasItem')->willReturn(true);
        $this->entityManager->expects($this->never())->method('getConnection');

        $article = new Article();
        $this->service->incrementView($article);
    }

    public function testIncrementViewSavesToCacheAndUpdatesDbOnFirstView(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getClientIp')->willReturn('192.168.1.1');
        $this->requestStack->method('getCurrentRequest')->willReturn($request);

        $this->cache->method('hasItem')->willReturn(false);

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->expects($this->once())->method('set')->with(true)->willReturnSelf();
        $cacheItem->expects($this->once())->method('expiresAfter')->with(3600)->willReturnSelf();
        $this->cache->method('getItem')->willReturn($cacheItem);
        $this->cache->expects($this->once())->method('save')->with($cacheItem);

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
            ->method('executeStatement')
            ->with(
                'UPDATE pna_article SET views = views + 1 WHERE id = :id',
                $this->anything(),
            );
        $this->entityManager->method('getConnection')->willReturn($connection);

        $article = new Article();
        $this->service->incrementView($article);
    }

    public function testCacheKeyIsUniquePerIpAndArticle(): void
    {
        $request1 = $this->createMock(Request::class);
        $request1->method('getClientIp')->willReturn('10.0.0.1');

        $request2 = $this->createMock(Request::class);
        $request2->method('getClientIp')->willReturn('10.0.0.2');

        $capturedKeys = [];
        $this->cache->method('hasItem')->willReturnCallback(function (string $key) use (&$capturedKeys): bool {
            $capturedKeys[] = $key;
            return true;
        });

        $article = new Article();

        $this->requestStack->method('getCurrentRequest')->willReturn($request1);
        $this->service->incrementView($article);

        $this->requestStack = $this->createMock(RequestStack::class);
        $this->requestStack->method('getCurrentRequest')->willReturn($request2);
        $service2 = new ViewCounterService($this->entityManager, $this->requestStack, $this->cache);
        $service2->incrementView($article);

        $this->assertCount(2, $capturedKeys);
        $this->assertNotSame($capturedKeys[0], $capturedKeys[1]);
    }
}
