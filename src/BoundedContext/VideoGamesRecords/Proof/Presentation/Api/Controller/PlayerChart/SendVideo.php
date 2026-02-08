<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Presentation\Api\Controller\PlayerChart;

use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Proof;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SendVideo extends AbstractController
{
    public function __construct(
        private readonly UserProvider $userProvider,
        private readonly EntityManagerInterface $em,
        private readonly PlayerChartRepository $playerChartRepository
    ) {
    }

    #[Route('/player-chart/{id}/proof/video', name: 'vgr_player_chart_send_video', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function __invoke(int $id, Request $request): Response
    {
        $playerChart = $this->playerChartRepository->find($id);

        if (!$playerChart) {
            return new JsonResponse(['error' => 'Score not found'], Response::HTTP_NOT_FOUND);
        }

        $player = $this->userProvider->getPlayer();

        if ($player === null) {
            return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        if ($playerChart->getPlayer()->getId() !== $player->getId()) {
            return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        if (!in_array($playerChart->getStatus(), PlayerChartStatusEnum::getStatusForProving())) {
            return new JsonResponse(['error' => 'Cannot add proof in current status'], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['url']) || empty($data['url'])) {
            return new JsonResponse(['error' => 'Video URL is required'], Response::HTTP_BAD_REQUEST);
        }

        $url = trim($data['url']);

        // Validate YouTube URL
        if (!$this->isValidYoutubeUrl($url)) {
            return new JsonResponse(['error' => 'Invalid YouTube URL'], Response::HTTP_BAD_REQUEST);
        }

        // Create video
        $video = new Video();
        $video->setUrl($url);
        $video->setPlayer($player);
        $video->setGame($playerChart->getChart()->getGroup()->getGame());
        $video->setTitle($playerChart->getChart()->getDefaultName() . ' - ' . $player->getPseudo());
        $this->em->persist($video);

        // Create proof
        $proof = new Proof();
        $proof->setVideo($video);
        $proof->setPlayer($player);
        $proof->setChart($playerChart->getChart());
        $this->em->persist($proof);

        // Update PlayerChart
        $playerChart->setProof($proof);
        if ($playerChart->getStatus() === PlayerChartStatusEnum::NONE) {
            $playerChart->setStatus(PlayerChartStatusEnum::PROOF_SENT);
        } else {
            $playerChart->setStatus(PlayerChartStatusEnum::REQUEST_PROOF_SENT);
        }

        $this->em->flush();

        return new JsonResponse([
            'success' => true,
            'proofId' => $proof->getId(),
            'videoId' => $video->getId(),
        ], Response::HTTP_CREATED);
    }

    private function isValidYoutubeUrl(string $url): bool
    {
        // Match various YouTube URL formats
        $patterns = [
            '/^https?:\/\/(www\.)?youtube\.com\/watch\?v=[\w-]+/',
            '/^https?:\/\/youtu\.be\/[\w-]+/',
            '/^https?:\/\/(www\.)?youtube\.com\/embed\/[\w-]+/',
            '/^https?:\/\/(www\.)?youtube\.com\/v\/[\w-]+/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }

        return false;
    }
}
