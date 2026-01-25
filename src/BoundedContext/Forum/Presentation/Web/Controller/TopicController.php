<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Presentation\Web\Controller;

use App\BoundedContext\Forum\Domain\Entity\Topic;
use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\MessageRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class TopicController extends AbstractLocalizedController
{
    private const int MESSAGES_PER_PAGE = 20;

    public function __construct(
        private readonly MessageRepository $messageRepository
    ) {
    }

    #[Route('/forum/{forumSlug}-f{forumId}/{slug}-t{id}', name: 'topic_show', requirements: ['forumId' => '\d+', 'id' => '\d+', 'forumSlug' => '[a-z0-9-]+', 'slug' => '[a-z0-9-]+'])]
    public function show(Request $request, Topic $topic, string $slug, string $forumSlug, int $forumId): Response
    {
        $forum = $topic->getForum();

        if ($topic->getSlug() !== $slug || $forum->getSlug() !== $forumSlug || $forum->getId() !== $forumId) {
            return $this->redirectToRoute('topic_show', [
                'id' => $topic->getId(),
                'slug' => $topic->getSlug(),
                'forumId' => $forum->getId(),
                'forumSlug' => $forum->getSlug(),
            ], 301);
        }

        $page = max(1, $request->query->getInt('page', 1));

        $query = $this->messageRepository->getMessagesByTopicQuery($topic);
        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * self::MESSAGES_PER_PAGE)
            ->setMaxResults(self::MESSAGES_PER_PAGE);

        $totalMessages = count($paginator);
        $totalPages = (int) ceil($totalMessages / self::MESSAGES_PER_PAGE);

        return $this->render('@Forum/topic/show.html.twig', [
            'topic' => $topic,
            'forum' => $forum,
            'messages' => $paginator,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalMessages' => $totalMessages,
        ]);
    }
}
