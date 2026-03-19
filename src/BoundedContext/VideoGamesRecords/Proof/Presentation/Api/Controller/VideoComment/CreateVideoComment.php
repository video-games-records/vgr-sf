<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Presentation\Api\Controller\VideoComment;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;
use App\BoundedContext\VideoGamesRecords\Proof\Application\Mapper\VideoCommentMapper;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\VideoComment;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateVideoComment extends AbstractController
{
    public function __construct(
        private readonly UserProvider $userProvider,
        private readonly EntityManagerInterface $em,
        private readonly VideoRepository $videoRepository,
        private readonly VideoCommentMapper $videoCommentMapper,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $player = $this->userProvider->getPlayer();

        if ($player === null) {
            return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['content'])) {
            return new JsonResponse(['error' => 'Content is required'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($data['video'])) {
            return new JsonResponse(['error' => 'Video is required'], Response::HTTP_BAD_REQUEST);
        }

        // Parse video IRI: "/api/videos/123" → 123
        $videoIri = $data['video'];
        if (!preg_match('/\/api\/videos\/(\d+)$/', $videoIri, $matches)) {
            return new JsonResponse(['error' => 'Invalid video IRI'], Response::HTTP_BAD_REQUEST);
        }

        $videoId = (int) $matches[1];
        $video = $this->videoRepository->find($videoId);

        if ($video === null) {
            return new JsonResponse(['error' => 'Video not found'], Response::HTTP_NOT_FOUND);
        }

        $comment = new VideoComment();
        $comment->setVideo($video);
        $comment->setPlayer($player);
        $comment->setContent(trim($data['content']));

        $this->em->persist($comment);
        $this->em->flush();

        return new JsonResponse(
            $this->videoCommentMapper->toDTO($comment),
            Response::HTTP_CREATED
        );
    }
}
