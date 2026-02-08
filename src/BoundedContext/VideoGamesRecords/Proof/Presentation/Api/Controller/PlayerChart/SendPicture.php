<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Presentation\Api\Controller\PlayerChart;

use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Picture;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Proof;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SendPicture extends AbstractController
{
    /** @var array<string, string> */
    private array $mimeToExtension = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
    ];

    public function __construct(
        private readonly FilesystemOperator $proofStorage,
        private readonly UserProvider $userProvider,
        private readonly EntityManagerInterface $em,
        private readonly PlayerChartRepository $playerChartRepository
    ) {
    }

    #[Route('/player-chart/{id}/proof/picture', name: 'vgr_player_chart_send_picture', methods: ['POST'])]
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

        if ($playerChart->getChart()->getIsProofVideoOnly()) {
            return new JsonResponse(['error' => 'Only video proofs are accepted for this chart'], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['file']) || empty($data['file'])) {
            return new JsonResponse(['error' => 'No file provided'], Response::HTTP_BAD_REQUEST);
        }

        $file = $data['file'];

        // Parse base64 data URL
        if (!preg_match('/^data:(image\/(png|jpeg|jpg));base64,(.+)$/', $file, $matches)) {
            return new JsonResponse(['error' => 'Invalid image format'], Response::HTTP_BAD_REQUEST);
        }

        $mimeType = $matches[1];
        $imageData = base64_decode($matches[3]);

        if ($imageData === false) { // @phpstan-ignore identical.alwaysFalse
            return new JsonResponse(['error' => 'Invalid base64 data'], Response::HTTP_BAD_REQUEST);
        }

        $extension = $this->mimeToExtension[$mimeType] ?? 'jpg';

        $idPlayer = $player->getId();
        $idGame = $playerChart->getChart()->getGroup()->getGame()->getId();

        // Check for duplicate image
        $hash = hash('sha256', $imageData);
        $existingPicture = $this->em->getRepository(Picture::class)->findOneBy([
            'hash' => $hash,
            'player' => $player,
            'game' => $playerChart->getChart()->getGroup()->getGame(),
        ]);

        if ($existingPicture === null) {
            // Create new picture
            $filename = $idPlayer . '/' . $idGame . '/' . uniqid() . '.' . $extension;

            $metadata = [
                'idplayer' => $idPlayer,
                'idgame' => $idGame,
            ];

            // Resize image using Intervention Image
            $manager = ImageManager::gd();
            $image = $manager->read($imageData);

            // Scale down if larger than 1600x1080, maintaining aspect ratio
            $image->scaleDown(1600, 1080);

            // Encode to appropriate format
            $encodedImage = match ($extension) {
                'png' => $image->toPng(),
                default => $image->toJpeg(85),
            };

            $this->proofStorage->write($filename, (string) $encodedImage);

            $picture = new Picture();
            $picture->setPath($filename);
            $picture->setMetadata(serialize($metadata));
            $picture->setPlayer($player);
            $picture->setGame($playerChart->getChart()->getGroup()->getGame());
            $picture->setHash($hash);
            $this->em->persist($picture);
        } else {
            $picture = $existingPicture;
        }

        // Create proof
        $proof = new Proof();
        $proof->setPicture($picture);
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
            'pictureId' => $picture->getId(),
        ], Response::HTTP_CREATED);
    }
}
