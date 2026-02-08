<?php

declare(strict_types=1);

namespace App\BoundedContext\Article\Presentation\Web\Controller;

use App\BoundedContext\Article\Infrastructure\Doctrine\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class TopNewsController extends AbstractController
{
    public const string CACHE_KEY = 'latest_news';

    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly CacheInterface $cache,
    ) {
    }

    public function latest(int $ttl = 0): Response
    {
        if ($ttl > 0) {
            $html = $this->cache->get(self::CACHE_KEY, function (ItemInterface $item) use ($ttl) {
                $item->expiresAfter($ttl);
                $articles = $this->articleRepository->findLatestPublished(5);

                return $this->renderView('@Article/news/_latest_news.html.twig', [
                    'articles' => $articles,
                ]);
            });

            return new Response($html);
        }

        $articles = $this->articleRepository->findLatestPublished(5);

        return $this->render('@Article/news/_latest_news.html.twig', [
            'articles' => $articles,
        ]);
    }
}
