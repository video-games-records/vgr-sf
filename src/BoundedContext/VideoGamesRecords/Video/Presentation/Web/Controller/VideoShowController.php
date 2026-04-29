<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Video\Presentation\Web\Controller;

use App\BoundedContext\VideoGamesRecords\Video\Domain\Entity\VideoComment;
use App\BoundedContext\VideoGamesRecords\Video\Infrastructure\Doctrine\Repository\VideoRepository;
use App\BoundedContext\VideoGamesRecords\Video\Infrastructure\Doctrine\Repository\VideoCommentRepository;
use App\BoundedContext\VideoGamesRecords\Video\Presentation\Form\Type\VideoCommentType;
use App\BoundedContext\VideoGamesRecords\Video\Application\Service\VideoRecommendationService;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\SharedKernel\Presentation\Web\Controller\AbstractLocalizedController;
use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class VideoShowController extends AbstractLocalizedController
{
    private const int COMMENTS_PER_PAGE = 10;

    public function __construct(
        private readonly VideoRepository $videoRepository,
        private readonly VideoCommentRepository $videoCommentRepository,
        private readonly PlayerRepository $playerRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly VideoRecommendationService $videoRecommendationService,
    ) {
    }

    #[Route('/video/{id}/{slug}', name: 'vgr_video_show', requirements: ['id' => '\d+'])]
    public function show(int $id, string $slug, Request $request): Response
    {
        $video = $this->videoRepository->findActiveByIdWithRelations($id);

        if (!$video) {
            throw $this->createNotFoundException('Video not found');
        }

        if ($video->getSlug() !== $slug) {
            return $this->redirectToRoute('vgr_video_show', [
                'id' => $id,
                'slug' => $video->getSlug(),
            ], 301);
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $paginator = $this->videoCommentRepository->findPaginatedByVideo($video, $page, self::COMMENTS_PER_PAGE);

        $totalComments = count($paginator);
        $totalPages = max(1, (int) ceil($totalComments / self::COMMENTS_PER_PAGE));

        // Comment form and edit forms
        $commentForm = null;
        $editForms = [];

        if ($this->isGranted('ROLE_USER')) {
            /** @var User $user */
            $user = $this->getUser();
            $player = $this->playerRepository->getPlayerFromUser($user);

            $commentForm = $this->createForm(VideoCommentType::class, null, [
                'action' => $this->generateUrl('vgr_video_comment_create', [
                    'id' => $video->getId(),
                    'slug' => $video->getSlug(),
                ]),
            ]);

            foreach ($paginator as $comment) {
                if ($player && $comment->getPlayer()->getId() === $player->getId()) {
                    $editForms[(int) $comment->getId()] = $this->createForm(VideoCommentType::class, null, [
                        'action' => $this->generateUrl('vgr_video_comment_edit', [
                            'id' => $video->getId(),
                            'slug' => $video->getSlug(),
                            'commentId' => $comment->getId(),
                        ]),
                    ])->createView();
                }
            }
        }

        // Vidéos recommandées avec algorithme intelligent
        $relatedVideos = $this->videoRecommendationService->getRelatedVideos($video, 10);

        return $this->render('@VideoGamesRecordsVideo/video/show.html.twig', [
            'video' => $video,
            'comments' => $paginator,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalComments' => $totalComments,
            'commentForm' => $commentForm?->createView(),
            'editForms' => $editForms,
            'related_videos' => $relatedVideos,
        ]);
    }


    #[Route(
        '/video/{id}/{slug}/comment',
        name: 'vgr_video_comment_create',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    #[IsGranted('ROLE_USER')]
    public function createComment(Request $request, int $id, string $slug): Response
    {
        $video = $this->videoRepository->findActiveByIdWithRelations($id);

        if (!$video) {
            throw $this->createNotFoundException('Video not found');
        }

        $form = $this->createForm(VideoCommentType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $player = $this->playerRepository->getPlayerFromUser($user);

            if (!$player) {
                throw $this->createAccessDeniedException('Player not found');
            }

            $comment = new VideoComment();
            $comment->setVideo($video);
            $comment->setPlayer($player);
            $comment->setContent($form->get('content')->getData());

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('comment.flash.created', [], 'VgrVideo'));

            return $this->redirectToRoute('vgr_video_show', [
                'id' => $video->getId(),
                'slug' => $video->getSlug(),
                '_fragment' => 'comment-' . $comment->getId(),
            ], 303);
        }

        $this->addFlash('danger', $this->translator->trans('comment.flash.error', [], 'VgrVideo'));

        return $this->redirectToRoute('vgr_video_show', [
            'id' => $id,
            'slug' => $slug,
        ], 303);
    }

    #[Route(
        '/video/{id}/{slug}/comment/{commentId}/edit',
        name: 'vgr_video_comment_edit',
        requirements: ['id' => '\d+', 'commentId' => '\d+'],
        methods: ['POST'],
    )]
    #[IsGranted('ROLE_USER')]
    public function editComment(Request $request, int $id, string $slug, int $commentId): Response
    {
        $video = $this->videoRepository->findActiveByIdWithRelations($id);

        if (!$video) {
            throw $this->createNotFoundException('Video not found');
        }

        $comment = $this->videoCommentRepository->find($commentId);

        if (!$comment || $comment->getVideo()->getId() !== $video->getId()) {
            throw $this->createNotFoundException('Comment not found');
        }

        /** @var User $user */
        $user = $this->getUser();
        /** @var Player $player */
        $player = $this->playerRepository->getPlayerFromUser($user);

        if ($comment->getPlayer()->getId() !== $player->getId()) {
            throw $this->createAccessDeniedException('You can only edit your own comments');
        }

        $form = $this->createForm(VideoCommentType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setContent($form->get('content')->getData());
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('comment.flash.updated', [], 'VgrVideo'));

            // Find the page where this comment is
            $page = $this->getCommentPage($comment);

            return $this->redirectToRoute('vgr_video_show', [
                'id' => $video->getId(),
                'slug' => $video->getSlug(),
                'page' => $page,
                '_fragment' => 'comment-' . $comment->getId(),
            ], 303);
        }

        $this->addFlash('danger', $this->translator->trans('comment.flash.error', [], 'VgrVideo'));

        return $this->redirectToRoute('vgr_video_show', [
            'id' => $id,
            'slug' => $slug,
        ], 303);
    }

    private function getCommentPage(VideoComment $comment): int
    {
        return $this->videoCommentRepository->getCommentPositionById($comment, self::COMMENTS_PER_PAGE);
    }
}
