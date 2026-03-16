<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Presentation\Web\Controller\Player;

use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerChart;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerChartRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Picture;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Proof;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class MyProofsUpload extends AbstractController
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

    #[Route('/my-proofs/upload/{id}', name: 'vgr_my_proofs_upload', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function __invoke(int $id, Request $request): Response
    {
        $playerChart = $this->playerChartRepository->find($id);

        if (!$playerChart) {
            throw $this->createNotFoundException('Score not found');
        }

        $player = $this->userProvider->getPlayer();

        if ($player === null || $playerChart->getPlayer()->getId() !== $player->getId()) {
            throw $this->createAccessDeniedException();
        }

        $isAjax = $request->isXmlHttpRequest();

        if (!in_array($playerChart->getStatus(), PlayerChartStatusEnum::getStatusForProving())) {
            if ($isAjax) {
                return new JsonResponse(['error' => 'my_proofs.upload_not_allowed'], Response::HTTP_FORBIDDEN);
            }
            $this->addFlash('error', 'my_proofs.upload_not_allowed');
            return $this->redirectToGame($playerChart);
        }

        if ($playerChart->getChart()->getIsProofVideoOnly()) {
            if ($isAjax) {
                return new JsonResponse(['error' => 'my_proofs.upload_not_allowed'], Response::HTTP_FORBIDDEN);
            }
            $this->addFlash('error', 'my_proofs.upload_not_allowed');
            return $this->redirectToGame($playerChart);
        }

        /** @var string|null $token */
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('my_proofs_upload_' . $id, $token)) {
            if ($isAjax) {
                return new JsonResponse(['error' => 'my_proofs.upload_error'], Response::HTTP_BAD_REQUEST);
            }
            $this->addFlash('error', 'my_proofs.upload_error');
            return $this->redirectToGame($playerChart);
        }

        $file = $request->files->get('proof_file');

        if (!$file || !$file->isValid()) {
            if ($isAjax) {
                return new JsonResponse(['error' => 'my_proofs.upload_invalid_file'], Response::HTTP_BAD_REQUEST);
            }
            $this->addFlash('error', 'my_proofs.upload_invalid_file');
            return $this->redirectToGame($playerChart);
        }

        $mimeType = $file->getMimeType();
        if (!isset($this->mimeToExtension[$mimeType])) {
            if ($isAjax) {
                return new JsonResponse(['error' => 'my_proofs.upload_invalid_file'], Response::HTTP_BAD_REQUEST);
            }
            $this->addFlash('error', 'my_proofs.upload_invalid_file');
            return $this->redirectToGame($playerChart);
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            if ($isAjax) {
                return new JsonResponse(['error' => 'my_proofs.upload_invalid_file'], Response::HTTP_BAD_REQUEST);
            }
            $this->addFlash('error', 'my_proofs.upload_invalid_file');
            return $this->redirectToGame($playerChart);
        }

        $extension = $this->mimeToExtension[$mimeType];
        $imageData = file_get_contents($file->getPathname());

        if ($imageData === false) {
            $this->addFlash('error', 'my_proofs.upload_invalid_file');
            return $this->redirectToGame($playerChart);
        }

        $idPlayer = $player->getId();
        $game = $playerChart->getChart()->getGroup()->getGame();
        $idGame = $game->getId();

        // Check for duplicate image
        $hash = hash('sha256', $imageData);
        $existingPicture = $this->em->getRepository(Picture::class)->findOneBy([
            'hash' => $hash,
            'player' => $player,
            'game' => $game,
        ]);

        if ($existingPicture === null) {
            $filename = $idPlayer . '/' . $idGame . '/' . uniqid() . '.' . $extension;

            $metadata = [
                'idplayer' => $idPlayer,
                'idgame' => $idGame,
            ];

            // Resize image using Intervention Image
            $manager = ImageManager::gd();
            $image = $manager->read($imageData);
            $image->scaleDown(1600, 1080);

            $encodedImage = match ($extension) {
                'png' => $image->toPng(),
                default => $image->toJpeg(85),
            };

            $this->proofStorage->write($filename, (string) $encodedImage);

            $picture = new Picture();
            $picture->setPath($filename);
            $picture->setMetadata(serialize($metadata));
            $picture->setPlayer($player);
            $picture->setGame($game);
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

        if ($isAjax) {
            return new Response(
                $this->renderView('@VideoGamesRecordsProof/player/_score_card.html.twig', [
                    'pc' => $playerChart,
                ])
            );
        }

        $this->addFlash('success', 'my_proofs.upload_success');

        return $this->redirectToGame($playerChart);
    }

    private function redirectToGame(PlayerChart $playerChart): Response
    {
        $game = $playerChart->getChart()->getGroup()->getGame();

        return $this->redirectToRoute('vgr_my_proofs_game', [
            'gameId' => $game->getId(),
            'gameSlug' => $game->getSlug(),
        ]);
    }
}
