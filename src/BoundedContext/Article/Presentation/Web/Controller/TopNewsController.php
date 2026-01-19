<?php

declare(strict_types=1);

namespace App\BoundedContext\Article\Presentation\Web\Controller;

use App\BoundedContext\Article\Infrastructure\Doctrine\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class TopNewsController extends AbstractController
{
    private ArticleRepository $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    public function latest(): Response
    {
        $articles = $this->articleRepository->findLatestPublished(5);

        return $this->render('@Article/news/_latest_news.html.twig', [
            'articles' => $articles
        ]);
    }
}
