<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Presentation\Web\Controller;

use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\Cache;
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
    #[Cache(maxage: 3600 * 24, public: true, mustRevalidate: true)]
    public function __invoke(Badge $badge): StreamedResponse
    {
        $path = $badge->getType()->getDirectory() . DIRECTORY_SEPARATOR . $badge->getPicture();
        if (!$this->appStorage->fileExists($path)) {
            $path = BadgeType::getDefaultDirectory() . DIRECTORY_SEPARATOR . 'default.gif';
        }

        $stream = $this->appStorage->readStream($path);
        $response = new StreamedResponse(function () use ($stream) {
            fpassthru($stream);
        }, 200, ['Content-Type' => 'image/gif']);
        $response->headers->set('Cache-Control', 'public, max-age=86400, immutable');
        return $response;
    }
}
