<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Presentation\Web\Controller;

use App\BoundedContext\Forum\Domain\Entity\Forum;
use App\BoundedContext\Forum\Domain\Entity\Message;
use App\BoundedContext\Forum\Domain\Entity\Topic;
use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\CategoryRepository;
use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\ForumUserLastVisitRepository;
use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\TopicRepository;
use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\TopicTypeRepository;
use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\TopicUserLastVisitRepository;
use App\BoundedContext\Forum\Presentation\Form\CreateTopicFormType;
use App\BoundedContext\User\Domain\Entity\User;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class ForumController extends AbstractLocalizedController
{
    private const int TOPICS_PER_PAGE = 20;
    private const int DEFAULT_TOPIC_TYPE_ID = 3;
    private const int RECENT_ACTIVITY_DAYS = 15;
    private const int RECENT_TOPICS_LIMIT = 50;

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly TopicRepository $topicRepository,
        private readonly TopicTypeRepository $topicTypeRepository,
        private readonly ForumUserLastVisitRepository $forumUserLastVisitRepository,
        private readonly TopicUserLastVisitRepository $topicUserLastVisitRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route('/forum', name: 'forum_index')]
    public function index(): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        $categories = $this->categoryRepository->findDisplayedOnHome();
        $recentTopics = $this->topicRepository->findWithRecentActivity(
            self::RECENT_ACTIVITY_DAYS,
            self::RECENT_TOPICS_LIMIT,
            $user
        );

        // Calculate hasNewContent for each forum
        if ($user !== null) {
            $forumVisits = $this->forumUserLastVisitRepository->findByUserIndexedByForum($user);

            foreach ($categories as $category) {
                foreach ($category->getForums() as $forum) {
                    $visit = $forumVisits[$forum->getId()] ?? null;
                    $lastMessage = $forum->getLastMessage();

                    if ($lastMessage === null) {
                        $forum->hasNewContent = false;
                    } elseif ($visit === null) {
                        // Never visited = has new content
                        $forum->hasNewContent = true;
                    } else {
                        $forum->hasNewContent = $lastMessage->getCreatedAt() > $visit->getLastVisitedAt();
                    }
                }
            }
        }

        return $this->render('@Forum/forum/index.html.twig', [
            'categories' => $categories,
            'recentTopics' => $recentTopics,
            'recentActivityDays' => self::RECENT_ACTIVITY_DAYS,
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

        /** @var User|null $user */
        $user = $this->getUser();

        $page = max(1, $request->query->getInt('page', 1));

        $query = $this->topicRepository->getActiveTopicsQuery($forum);
        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * self::TOPICS_PER_PAGE)
            ->setMaxResults(self::TOPICS_PER_PAGE);

        $totalTopics = count($paginator);
        $totalPages = (int) ceil($totalTopics / self::TOPICS_PER_PAGE);

        // Calculate hasNewContent for each topic
        $topics = iterator_to_array($paginator);
        if ($user !== null) {
            $topicVisits = $this->topicUserLastVisitRepository->findByUserAndForumIndexedByTopic($user, $forum);

            foreach ($topics as $topic) {
                $visit = $topicVisits[$topic->getId()] ?? null;
                $lastMessage = $topic->getLastMessage();

                if ($lastMessage === null) {
                    $topic->hasNewContent = false;
                } elseif ($visit === null) {
                    // Never visited = has new content
                    $topic->hasNewContent = true;
                } else {
                    $topic->hasNewContent = $lastMessage->getCreatedAt() > $visit->getLastVisitedAt();
                }
            }
        }

        return $this->render('@Forum/forum/show.html.twig', [
            'forum' => $forum,
            'topics' => $topics,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalTopics' => $totalTopics,
        ]);
    }

    #[Route('/forum/{slug}-f{id}/new-topic', name: 'forum_create_topic', requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+'])]
    #[IsGranted('ROLE_USER')]
    public function createTopic(Request $request, Forum $forum, string $slug): Response
    {
        if ($forum->getSlug() !== $slug) {
            return $this->redirectToRoute('forum_create_topic', [
                'id' => $forum->getId(),
                'slug' => $forum->getSlug(),
            ], 301);
        }

        $isAdmin = $this->isGranted('ROLE_ADMIN');

        $form = $this->createForm(CreateTopicFormType::class, null, [
            'is_admin' => $isAdmin,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();

            $topic = new Topic();
            $topic->setName($form->get('name')->getData());
            $topic->setForum($forum);
            $topic->setUser($user);

            if ($isAdmin && $form->has('type') && $form->get('type')->getData()) {
                $topic->setType($form->get('type')->getData());
            } else {
                $defaultType = $this->topicTypeRepository->find(self::DEFAULT_TOPIC_TYPE_ID);
                $topic->setType($defaultType);
            }

            $message = new Message();
            $message->setTopic($topic);
            $message->setUser($user);
            $message->setMessage($form->get('message')->getData());

            $topic->addMessage($message);

            $this->entityManager->persist($topic);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('topic.create.success', [], 'Forum'));

            return $this->redirectToRoute('topic_show', [
                'id' => $topic->getId(),
                'slug' => $topic->getSlug(),
                'forumId' => $forum->getId(),
                'forumSlug' => $forum->getSlug(),
            ]);
        }

        return $this->render('@Forum/forum/create_topic.html.twig', [
            'forum' => $forum,
            'form' => $form->createView(),
        ]);
    }
}
