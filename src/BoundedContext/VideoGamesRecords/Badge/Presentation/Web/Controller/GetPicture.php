<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Presentation\Web\Controller;

use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\Badge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;

class GetPicture extends AbstractController
{
    private FilesystemOperator $appStorage;

    public function __construct(FilesystemOperator $appStorage)
    {
        $this->appStorage = $appStorage;
    }

    #[Route(
        '/badge/{id}/picture',
        name: 'vgr_badge_picture',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET']
    )]
    public function __invoke(Badge $badge, Request $request): StreamedResponse
    {
        $path = $badge->getType()->getDirectory() . DIRECTORY_SEPARATOR . $badge->getPicture();
        if (!$this->appStorage->fileExists($path)) {
            $path = BadgeType::getDefaultDirectory() . DIRECTORY_SEPARATOR . 'default.gif';
        }

        $lastModified = $this->appStorage->lastModified($path);
        $etag = '"' . md5($path . $lastModified) . '"';

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'image/gif');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
        $response->headers->set('ETag', $etag);

        $ifNoneMatch = $request->headers->get('If-None-Match');
        $ifModifiedSince = $request->headers->get('If-Modified-Since');
        $notModified = ($ifNoneMatch !== null && $ifNoneMatch === $etag)
            || ($ifNoneMatch === null && $ifModifiedSince !== null && strtotime($ifModifiedSince) >= $lastModified);

        if ($notModified) {
            $response->setStatusCode(304);
            return $response;
        }

        $stream = $this->appStorage->readStream($path);
        $response->setCallback(function () use ($stream) {
            fpassthru($stream);
        });

        return $response;
    }
}
