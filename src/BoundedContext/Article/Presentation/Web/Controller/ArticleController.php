<?php

declare(strict_types=1);

namespace App\BoundedContext\Article\Presentation\Web\Controller;

use App\BoundedContext\Article\Application\Service\ViewCounterService;
use App\BoundedContext\Article\Infrastructure\Doctrine\Repository\ArticleRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class ArticleController extends AbstractLocalizedController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly ViewCounterService $viewCounterService
    ) {
    }

    #[Route('/article/{id}-{slug}', name: 'article_show', requirements: ['id' => '\d+'])]
    public function show(Request $request, int $id, string $slug): Response
    {
        $article = $this->articleRepository->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        // Verify slug matches (redirect if different)
        $currentLocale = $request->getLocale();
        $translation = $article->translate($currentLocale);

        if ($translation && $article->getSlug() !== $slug) {
            return $this->redirectToRoute('article_show', [
                'id' => $id,
                'slug' => $article->getSlug(),
            ], 301);
        }

        // Increment view count (with IP-based caching)
        $this->viewCounterService->incrementView($article);

        return $this->render('@Article/article/show.html.twig', [
            'article' => $article,
            'translation' => $translation,
        ]);
    }
}
