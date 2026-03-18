<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
    public function game(Game $game, Request $request): StreamedResponse
    {
        $picture = $game->getPicture();
        $path = $picture ? 'game/' . $picture : null;
        return $this->serveFile($path, $this->projectDir . '/assets/img/default/game.png', $request);
    }

    #[Route(
        '/serie/{id}/picture',
        name: 'vgr_core_serie_picture',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET']
    )]
    public function serie(Serie $serie, Request $request): StreamedResponse
    {
        $picture = $serie->getPicture();
        $path = $picture ? 'series/picture/' . $picture : null;
        return $this->serveFile($path, $this->projectDir . '/assets/img/default/serie.png', $request);
    }

    private function serveFile(?string $path, string $defaultAssetPath, Request $request): StreamedResponse
    {
        if ($path !== null && $this->appStorage->fileExists($path)) {
            $lastModified = $this->appStorage->lastModified($path);
            $etag = '"' . md5($path . $lastModified) . '"';

            $response = new StreamedResponse();
            $response->headers->set('Content-Type', 'image/png');
            $response->headers->set('Cache-Control', 'public, max-age=86400, must-revalidate');
            $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
            $response->headers->set('ETag', $etag);

            if ($this->isNotModified($request, $etag, $lastModified)) {
                $response->setStatusCode(304);
                return $response;
            }

            $stream = $this->appStorage->readStream($path);
            $response->setCallback(function () use ($stream) {
                fpassthru($stream);
            });

            return $response;
        }

        // Default image: never cache so that a newly uploaded picture shows immediately
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'image/png');
        $response->headers->set('Cache-Control', 'no-store');

        $response->setCallback(function () use ($defaultAssetPath) {
            $handle = fopen($defaultAssetPath, 'rb');
            if ($handle !== false) {
                fpassthru($handle);
                fclose($handle);
            }
        });

        return $response;
    }

    private function isNotModified(Request $request, string $etag, int $lastModified): bool
    {
        $ifNoneMatch = $request->headers->get('If-None-Match');
        if ($ifNoneMatch !== null) {
            return $ifNoneMatch === $etag;
        }

        $ifModifiedSince = $request->headers->get('If-Modified-Since');
        if ($ifModifiedSince !== null) {
            return strtotime($ifModifiedSince) >= $lastModified;
        }

        return false;
    }
}
