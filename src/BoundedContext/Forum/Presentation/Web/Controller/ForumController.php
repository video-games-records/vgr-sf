<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Presentation\Web\Controller;

use App\BoundedContext\Forum\Domain\Entity\Forum;
use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\CategoryRepository;
use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\TopicRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class ForumController extends AbstractLocalizedController
{
    private const int TOPICS_PER_PAGE = 20;

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly TopicRepository $topicRepository
    ) {
    }

    #[Route('/forum', name: 'forum_index')]
    public function index(): Response
    {
        $categories = $this->categoryRepository->findDisplayedOnHome();

        return $this->render('@Forum/forum/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/forum/{slug}-f{id}', name: 'forum_show', requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+'])]
    public function show(Request $request, Forum $forum, string $slug): Response
    {
        if ($forum->getSlug() !== $slug) {
            return $this->redirectToRoute('forum_show', [
                'id' => $forum->getId(),
                'slug' => $forum->getSlug(),
            ], 301);
        }

        $page = max(1, $request->query->getInt('page', 1));

        $query = $this->topicRepository->getActiveTopicsQuery($forum);
        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * self::TOPICS_PER_PAGE)
            ->setMaxResults(self::TOPICS_PER_PAGE);

        $totalTopics = count($paginator);
        $totalPages = (int) ceil($totalTopics / self::TOPICS_PER_PAGE);

        return $this->render('@Forum/forum/show.html.twig', [
            'forum' => $forum,
            'topics' => $paginator,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalTopics' => $totalTopics,
        ]);
    }
}
