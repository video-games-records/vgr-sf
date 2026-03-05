<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Presentation\Web\Controller;

use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Picture;

class PictureShow extends AbstractController
{
    private FilesystemOperator $proofStorage;

    /** @var array<string, string> */
    private array $extensions = [
        'png' => 'image/png',
        'jpg' => 'image/jpeg'
    ];

    public function __construct(FilesystemOperator $proofStorage, private readonly string $projectDir)
    {
        $this->proofStorage = $proofStorage;
    }

    #[Route(
        '/picture/{id}/show',
        name: 'vgr_proof_picture_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET']
    )]
    #[Cache(maxage: 3600 * 24, public: true, mustRevalidate: true)]
    public function __invoke(Picture $picture): StreamedResponse
    {
        if ($this->proofStorage->fileExists($picture->getPath())) {
            $stream = $this->proofStorage->readStream($picture->getPath());
            return new StreamedResponse(function () use ($stream) {
                fpassthru($stream);
                exit();
            }, 200, ['Content-Type' => $this->getMimeType($picture->getPath())]);
        }

        $defaultPath = $this->projectDir . '/assets/img/default/proof.png';
        return new StreamedResponse(function () use ($defaultPath) {
            $handle = fopen($defaultPath, 'rb');
            if ($handle !== false) {
                fpassthru($handle);
                fclose($handle);
            }
            exit();
        }, 200, ['Content-Type' => 'image/png']);
    }

    private function getMimeType(string $file): string
    {
        $infos = pathinfo($file);
        return $this->extensions[$infos['extension'] ?? 'png'] ?? 'image/png';
    }
}
