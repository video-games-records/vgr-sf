<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

class PictureController
{
    private FilesystemOperator $appStorage;

    public function __construct(FilesystemOperator $appStorage, private readonly string $projectDir)
    {
        $this->appStorage = $appStorage;
    }

    #[Route(
        '/game/{id}/picture',
        name: 'vgr_core_game_picture',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET']
    )]
    #[Cache(maxage: 31536000, public: true)]
    public function game(Game $game): StreamedResponse
    {
        $response = $this->getFile('game/' . $game->getPicture(), $this->projectDir . '/assets/img/default/game.png');
        $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        return $response;
    }

    #[Route(
        '/serie/{id}/picture',
        name: 'vgr_core_serie_picture',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET']
    )]
    #[Cache(maxage: 31536000, public: true)]
    public function serie(Serie $serie): StreamedResponse
    {
        $response = $this->getFile('series/picture/' . $serie->getPicture(), $this->projectDir . '/assets/img/default/serie.png');
        $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        return $response;
    }

    private function getFile(string $path, string $defaultAssetPath): StreamedResponse
    {
        if ($this->appStorage->fileExists($path)) {
            $stream = $this->appStorage->readStream($path);
            return new StreamedResponse(function () use ($stream) {
                fpassthru($stream);
            }, 200, ['Content-Type' => 'image/png']);
        }

        return new StreamedResponse(function () use ($defaultAssetPath) {
            $handle = fopen($defaultAssetPath, 'rb');
            if ($handle !== false) {
                fpassthru($handle);
                fclose($handle);
            }
        }, 200, ['Content-Type' => 'image/png']);
    }
}
