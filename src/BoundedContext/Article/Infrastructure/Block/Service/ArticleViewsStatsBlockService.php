<?php

declare(strict_types=1);

namespace App\BoundedContext\Article\Infrastructure\Block\Service;

use App\BoundedContext\Article\Application\Service\ArticleAnalyticsService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

class ArticleViewsStatsBlockService extends AbstractBlockService
{
    public function __construct(
        Environment $twig,
        private readonly ArticleAnalyticsService $analyticsService
    ) {
        parent::__construct($twig);
    }

    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        $stats = $this->analyticsService->getGlobalStats();
        $mostViewed = $this->analyticsService->getMostViewedArticles(5);
        $trending = $this->analyticsService->getTrendingArticles(7, 3);

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'stats' => $stats,
            'most_viewed' => $mostViewed,
            'trending' => $trending,
            'translation_domain' => 'Article',
        ], $response);
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => '@Article/admin/block/views_stats.html.twig',
            'title' => 'Statistiques des vues',
            'translation_domain' => 'Article',
        ]);
    }

    public function getName(): string
    {
        return 'Article Views Statistics';
    }
}
