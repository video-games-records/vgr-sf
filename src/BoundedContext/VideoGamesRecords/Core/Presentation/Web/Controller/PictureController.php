<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

class PictureController
{
    private FilesystemOperator $appStorage;

    public function __construct(FilesystemOperator $appStorage)
    {
        $this->appStorage = $appStorage;
    }

    /**
     * @throws FilesystemException
     */
    #[Route(
        '/game/{id}/picture',
        name: 'vgr_core_game_picture',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET']
    )]
    #[Cache(maxage: 31536000, public: true)]
    public function game(Game $game): StreamedResponse
    {
        $prefix = 'game/';
        $response = $this->getFile($prefix . $game->getPicture(), $prefix . 'default.png');
        $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        return $response;
    }

    /**
     * @throws FilesystemException
     */
    #[Route(
        '/serie/{id}/picture',
        name: 'vgr_core_serie_picture',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET']
    )]
    #[Cache(maxage: 31536000, public: true)]
    public function serie(Serie $serie): StreamedResponse
    {
        $prefix = 'series/picture/';
        $response = $this->getFile($prefix . $serie->getPicture(), $prefix . 'default.png');
        $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        return $response;
    }


    /**
     * @throws FilesystemException
     */
    private function getFile(string $path, string $default): StreamedResponse
    {
        if (!$this->appStorage->fileExists($path)) {
            $path = $default;
        }

        $stream = $this->appStorage->readStream($path);
        return new StreamedResponse(function () use ($stream) {
            fpassthru($stream);
        }, 200, ['Content-Type' => 'image/png']);
    }
}
