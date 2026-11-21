<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Video\Presentation\Api\Controller\Video;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;
use App\BoundedContext\VideoGamesRecords\Video\Application\Mapper\VideoMapper;
use App\BoundedContext\VideoGamesRecords\Video\Domain\Entity\Video;
use App\BoundedContext\VideoGamesRecords\Video\Infrastructure\Doctrine\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateVideo extends AbstractController
{
    public function __construct(
        private readonly UserProvider $userProvider,
        private readonly EntityManagerInterface $em,
        private readonly VideoMapper $videoMapper,
        private readonly VideoRepository $videoRepository,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $player = $this->userProvider->getPlayer();

        if ($player === null) {
            return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['url']) || empty($data['url'])) {
            return new JsonResponse(['error' => 'URL is required'], Response::HTTP_BAD_REQUEST);
        }

        $url = trim($data['url']);

        $video = new Video();
        $video->setUrl($url);

        $existingVideo = $this->videoRepository->findOneByTypeAndExternalId(
            $video->getType(),
            $video->getExternalId()
        );

        if ($existingVideo !== null) {
            return new JsonResponse(
                $this->videoMapper->toDTO($existingVideo),
                Response::HTTP_OK
            );
        }

        $video->setPlayer($player);

        if (!empty($data['game_id'])) {
            $game = $this->em->getRepository(Game::class)->find($data['game_id']);
            if (!$game) {
                return new JsonResponse(['error' => 'Game not found'], Response::HTTP_NOT_FOUND);
            }
            $video->setGame($game);
        }

        $this->em->persist($video);
        $this->em->flush();

        return new JsonResponse(
            $this->videoMapper->toDTO($video),
            Response::HTTP_CREATED
        );
    }
}
