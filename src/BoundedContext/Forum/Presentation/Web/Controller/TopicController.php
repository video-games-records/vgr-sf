<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Presentation\Web\Controller;

use App\BoundedContext\Forum\Application\Service\TopicReadService;
use App\BoundedContext\Forum\Domain\Entity\Message;
use App\BoundedContext\Forum\Domain\Entity\Topic;
use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\MessageRepository;
use App\BoundedContext\Forum\Infrastructure\Security\Voter\ForumVoter;
use App\BoundedContext\Forum\Presentation\Form\ReplyType;
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
class TopicController extends AbstractLocalizedController
{
    private const int MESSAGES_PER_PAGE = 20;

    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly TopicReadService $topicReadService
    ) {
    }

    #[Route('/forum/{forumSlug}-f{forumId}/{slug}-t{id}', name: 'topic_show', requirements: ['forumId' => '\d+', 'id' => '\d+', 'forumSlug' => '[a-z0-9-]+', 'slug' => '[a-z0-9-]+'])]
    public function show(Request $request, Topic $topic, string $slug, string $forumSlug, int $forumId): Response
    {
        $forum = $topic->getForum();

        $this->denyAccessUnlessGranted(ForumVoter::VIEW, $forum);

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

        $replyForm = null;
        if ($this->isGranted('ROLE_USER')) {
            /** @var User $user */
            $user = $this->getUser();
            $this->topicReadService->markTopicAsRead($user, $topic);

            $replyForm = $this->createForm(ReplyType::class, null, [
                'action' => $this->generateUrl('topic_reply', [
                    'id' => $topic->getId(),
                    'slug' => $topic->getSlug(),
                    'forumId' => $forum->getId(),
                    'forumSlug' => $forum->getSlug(),
                ]),
            ]);
        }

        return $this->render('@Forum/topic/show.html.twig', [
            'topic' => $topic,
            'forum' => $forum,
            'messages' => $paginator,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalMessages' => $totalMessages,
            'replyForm' => $replyForm?->createView(),
        ]);
    }

    #[Route('/forum/{forumSlug}-f{forumId}/{slug}-t{id}/reply', name: 'topic_reply', requirements: ['forumId' => '\d+', 'id' => '\d+', 'forumSlug' => '[a-z0-9-]+', 'slug' => '[a-z0-9-]+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function reply(Request $request, Topic $topic, string $slug, string $forumSlug, int $forumId): Response
    {
        $forum = $topic->getForum();

        $this->denyAccessUnlessGranted(ForumVoter::VIEW, $forum);

        if ($topic->getSlug() !== $slug || $forum->getSlug() !== $forumSlug || $forum->getId() !== $forumId) {
            return $this->redirectToRoute('topic_show', [
                'id' => $topic->getId(),
                'slug' => $topic->getSlug(),
                'forumId' => $forum->getId(),
                'forumSlug' => $forum->getSlug(),
            ], 301);
        }

        $form = $this->createForm(ReplyType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();

            $message = new Message();
            $message->setTopic($topic);
            $message->setUser($user);
            $message->setMessage($form->get('message')->getData());

            $this->entityManager->persist($message);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('topic.reply.success', [], 'Forum'));

            $lastPage = (int) ceil(($topic->getNbMessage()) / self::MESSAGES_PER_PAGE);

            return $this->redirectToRoute('topic_show', [
                'id' => $topic->getId(),
                'slug' => $topic->getSlug(),
                'forumId' => $forum->getId(),
                'forumSlug' => $forum->getSlug(),
                'page' => $lastPage,
                '_fragment' => 'message-' . $message->getId(),
            ], 303);
        }

        return $this->redirectToRoute('topic_show', [
            'id' => $topic->getId(),
            'slug' => $topic->getSlug(),
            'forumId' => $forum->getId(),
            'forumSlug' => $forum->getSlug(),
        ]);
    }
}
