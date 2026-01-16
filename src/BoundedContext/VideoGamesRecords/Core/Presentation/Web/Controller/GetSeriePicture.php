<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller;

use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;

class GetSeriePicture extends AbstractController
{
    private FilesystemOperator $appStorage;

    private string $prefix = 'series/picture/';

    public function __construct(FilesystemOperator $appStorage)
    {
        $this->appStorage = $appStorage;
    }

    #[Route(
        '/serie/{id}/picture',
        name: 'vgr_core_serie_picture',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET']
    )]
    #[Cache(public: true, maxage: 3600 * 24, mustRevalidate: true)]
    public function __invoke(Serie $serie): StreamedResponse
    {
        $path = $this->prefix . $serie->getPicture();
        if (!$this->appStorage->fileExists($path)) {
            $path = $this->prefix . 'default.png';
        }

        $stream = $this->appStorage->readStream($path);
        return new StreamedResponse(function () use ($stream) {
            fpassthru($stream);
        }, 200, ['Content-Type' => 'image/png']);
    }
}
