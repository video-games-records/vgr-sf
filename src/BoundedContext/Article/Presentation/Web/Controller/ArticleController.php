<?php

declare(strict_types=1);

namespace App\BoundedContext\Article\Presentation\Web\Controller;

use App\BoundedContext\Article\Application\Service\ViewCounterService;
use App\BoundedContext\Article\Domain\Entity\Comment;
use App\BoundedContext\Article\Infrastructure\Doctrine\Repository\ArticleRepository;
use App\BoundedContext\Article\Infrastructure\Doctrine\Repository\CommentRepository;
use App\BoundedContext\Article\Presentation\Form\Type\CommentType;
use App\BoundedContext\User\Domain\Entity\User;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class ArticleController extends AbstractLocalizedController
{
    private const int COMMENTS_PER_PAGE = 10;

    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly CommentRepository $commentRepository,
        private readonly ViewCounterService $viewCounterService,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
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

        // Paginated comments
        $page = max(1, $request->query->getInt('page', 1));
        $query = $this->commentRepository->getCommentsByArticleQuery($article);
        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * self::COMMENTS_PER_PAGE)
            ->setMaxResults(self::COMMENTS_PER_PAGE);

        $totalComments = count($paginator);
        $totalPages = max(1, (int) ceil($totalComments / self::COMMENTS_PER_PAGE));

        // Comment form and edit forms
        $commentForm = null;
        $editForms = [];

        if ($this->isGranted('ROLE_USER')) {
            /** @var User $user */
            $user = $this->getUser();

            $commentForm = $this->createForm(CommentType::class, null, [
                'action' => $this->generateUrl('article_comment_create', [
                    'id' => $article->getId(),
                    'slug' => $article->getSlug(),
                ]),
            ]);

            foreach ($paginator as $comment) {
                if ($comment->getUser()->getId() === $user->getId()) {
                    $editForms[$comment->getId()] = $this->createForm(CommentType::class, null, [
                        'action' => $this->generateUrl('article_comment_edit', [
                            'id' => $article->getId(),
                            'slug' => $article->getSlug(),
                            'commentId' => $comment->getId(),
                        ]),
                    ])->createView();
                }
            }
        }

        return $this->render('@Article/article/show.html.twig', [
            'article' => $article,
            'translation' => $translation,
            'comments' => $paginator,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalComments' => $totalComments,
            'commentForm' => $commentForm?->createView(),
            'editForms' => $editForms,
        ]);
    }

    #[Route(
        '/article/{id}-{slug}/comment',
        name: 'article_comment_create',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    #[IsGranted('ROLE_USER')]
    public function createComment(Request $request, int $id, string $slug): Response
    {
        $article = $this->articleRepository->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $form = $this->createForm(CommentType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();

            $comment = new Comment();
            $comment->setArticle($article);
            $comment->setUser($user);
            $comment->setContent($form->get('content')->getData());

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('comment.flash.created', [], 'Article'));

            $lastPage = max(1, (int) ceil($article->getNbComment() / self::COMMENTS_PER_PAGE));

            return $this->redirectToRoute('article_show', [
                'id' => $article->getId(),
                'slug' => $article->getSlug(),
                'page' => $lastPage,
                '_fragment' => 'comment-' . $comment->getId(),
            ], 303);
        }

        $this->addFlash('danger', $this->translator->trans('comment.flash.error', [], 'Article'));

        return $this->redirectToRoute('article_show', [
            'id' => $article->getId(),
            'slug' => $article->getSlug(),
        ], 303);
    }

    #[Route(
        '/article/{id}-{slug}/comment/{commentId}/edit',
        name: 'article_comment_edit',
        requirements: ['id' => '\d+', 'commentId' => '\d+'],
        methods: ['POST'],
    )]
    #[IsGranted('ROLE_USER')]
    public function editComment(Request $request, int $id, string $slug, int $commentId): Response
    {
        $article = $this->articleRepository->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $comment = $this->commentRepository->find($commentId);

        if (!$comment || $comment->getArticle()->getId() !== $article->getId()) {
            throw $this->createNotFoundException('Comment not found');
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($comment->getUser()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('You can only edit your own comments');
        }

        $form = $this->createForm(CommentType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setContent($form->get('content')->getData());
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('comment.flash.updated', [], 'Article'));

            // Find the page where this comment is
            $page = $this->getCommentPage($comment);

            return $this->redirectToRoute('article_show', [
                'id' => $article->getId(),
                'slug' => $article->getSlug(),
                'page' => $page,
                '_fragment' => 'comment-' . $comment->getId(),
            ], 303);
        }

        $this->addFlash('danger', $this->translator->trans('comment.flash.error', [], 'Article'));

        return $this->redirectToRoute('article_show', [
            'id' => $article->getId(),
            'slug' => $article->getSlug(),
        ], 303);
    }

    private function getCommentPage(Comment $comment): int
    {
        $qb = $this->commentRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.article = :article')
            ->andWhere('c.createdAt <= :createdAt')
            ->setParameter('article', $comment->getArticle())
            ->setParameter('createdAt', $comment->getCreatedAt());

        $position = (int) $qb->getQuery()->getSingleScalarResult();

        return max(1, (int) ceil($position / self::COMMENTS_PER_PAGE));
    }
}
