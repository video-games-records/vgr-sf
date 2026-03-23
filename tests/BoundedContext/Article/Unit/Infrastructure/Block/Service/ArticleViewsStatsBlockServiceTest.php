<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\Article\Unit\Infrastructure\Block\Service;

use App\BoundedContext\Article\Application\Service\ArticleAnalyticsService;
use App\BoundedContext\Article\Infrastructure\Block\Service\ArticleViewsStatsBlockService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

#[AllowMockObjectsWithoutExpectations]
class ArticleViewsStatsBlockServiceTest extends TestCase
{
    private ArticleAnalyticsService&MockObject $analyticsService;
    private Environment&Stub $twig;
    private ArticleViewsStatsBlockService $blockService;

    protected function setUp(): void
    {
        $this->twig = $this->createStub(Environment::class);
        $this->analyticsService = $this->createMock(ArticleAnalyticsService::class);
        $this->blockService = new ArticleViewsStatsBlockService($this->twig, $this->analyticsService);
    }

    // ------------------------------------------------------------------
    // getName
    // ------------------------------------------------------------------

    public function testGetNameReturnsExpectedName(): void
    {
        $this->assertSame('Article Views Statistics', $this->blockService->getName());
    }

    // ------------------------------------------------------------------
    // configureSettings
    // ------------------------------------------------------------------

    public function testConfigureSettingsSetsDefaultTemplate(): void
    {
        $resolver = new OptionsResolver();
        $this->blockService->configureSettings($resolver);

        $defaults = $resolver->resolve();

        $this->assertSame('@Article/admin/block/views_stats.html.twig', $defaults['template']);
    }

    public function testConfigureSettingsSetsDefaultTitle(): void
    {
        $resolver = new OptionsResolver();
        $this->blockService->configureSettings($resolver);

        $defaults = $resolver->resolve();

        $this->assertSame('Statistiques des vues', $defaults['title']);
    }

    public function testConfigureSettingsSetsTranslationDomain(): void
    {
        $resolver = new OptionsResolver();
        $this->blockService->configureSettings($resolver);

        $defaults = $resolver->resolve();

        $this->assertSame('Article', $defaults['translation_domain']);
    }

    // ------------------------------------------------------------------
    // execute
    // ------------------------------------------------------------------

    public function testExecuteCallsAnalyticsServiceMethods(): void
    {
        $stats = ['total_views' => 1000, 'total_articles' => 10, 'average_views_per_article' => 100.0, 'most_viewed_today' => null];
        $mostViewed = [['id' => 1, 'title' => 'Top Article', 'views' => 500]];
        $trending = [['id' => 2, 'title' => 'Trending', 'daily_avg_views' => 30.0]];

        $this->analyticsService->expects($this->once())
            ->method('getGlobalStats')
            ->willReturn($stats);
        $this->analyticsService->expects($this->once())
            ->method('getMostViewedArticles')
            ->with(5)
            ->willReturn($mostViewed);
        $this->analyticsService->expects($this->once())
            ->method('getTrendingArticles')
            ->with(7, 3)
            ->willReturn($trending);

        $block = $this->createMock(BlockInterface::class);
        $blockContext = $this->createMock(BlockContextInterface::class);
        $blockContext->method('getTemplate')->willReturn('@Article/admin/block/views_stats.html.twig');
        $blockContext->method('getBlock')->willReturn($block);
        $blockContext->method('getSettings')->willReturn([]);

        $this->twig->method('render')->willReturn('<div>stats</div>');

        $response = $this->blockService->execute($blockContext);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testExecutePassesCorrectVariablesToTemplate(): void
    {
        $stats = ['total_views' => 500, 'total_articles' => 5, 'average_views_per_article' => 100.0, 'most_viewed_today' => null];
        $mostViewed = [];
        $trending = [];

        $this->analyticsService->method('getGlobalStats')->willReturn($stats);
        $this->analyticsService->method('getMostViewedArticles')->willReturn($mostViewed);
        $this->analyticsService->method('getTrendingArticles')->willReturn($trending);

        $block = $this->createMock(BlockInterface::class);
        $blockContext = $this->createMock(BlockContextInterface::class);
        $blockContext->method('getTemplate')->willReturn('@Article/admin/block/views_stats.html.twig');
        $blockContext->method('getBlock')->willReturn($block);
        $blockContext->method('getSettings')->willReturn([]);

        $capturedTemplate = null;
        $capturedParams = [];

        $this->twig->method('render')
            ->willReturnCallback(function (string $template, array $params) use (&$capturedTemplate, &$capturedParams): string {
                $capturedTemplate = $template;
                $capturedParams = $params;
                return '';
            });

        $this->blockService->execute($blockContext);

        $this->assertSame('@Article/admin/block/views_stats.html.twig', $capturedTemplate);
        $this->assertArrayHasKey('stats', $capturedParams);
        $this->assertArrayHasKey('most_viewed', $capturedParams);
        $this->assertArrayHasKey('trending', $capturedParams);
        $this->assertArrayHasKey('translation_domain', $capturedParams);
        $this->assertSame('Article', $capturedParams['translation_domain']);
        $this->assertSame($stats, $capturedParams['stats']);
        $this->assertSame($mostViewed, $capturedParams['most_viewed']);
        $this->assertSame($trending, $capturedParams['trending']);
    }
}
