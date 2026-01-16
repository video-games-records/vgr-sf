<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Google\Service\Exception;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\DataProvider\YoutubeProvider;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Video;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\VideoType;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Video::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Video::class)]
class VideoListener
{
    private YoutubeProvider $youtubeProvider;
    private TranslatorInterface $translator;

    /**
     * @param YoutubeProvider     $youtubeProvider
     * @param TranslatorInterface $translator
     */
    public function __construct(
        YoutubeProvider $youtubeProvider,
        TranslatorInterface $translator
    ) {
        $this->youtubeProvider = $youtubeProvider;
        $this->translator = $translator;
    }

    /**
     * @param Video $video
     * @throws Exception
     */
    public function prePersist(Video $video): void
    {
        $video->getPlayer()->setNbVideo($video->getPlayer()->getNbVideo() + 1);

        $video->getGame()
            ?->setNbVideo(
                $video->getGame()
                    ->getNbVideo() + 1
            );

        // Set youtube data
        if ($video->getVideoType()->getValue() === VideoType::YOUTUBE) {
            $response = $this->youtubeProvider->getVideo($video->getExternalId());

            if (count($response->getItems()) == 0) {
                throw new BadRequestException($this->translator->trans('video.youtube.not_found'));
            }

            $youtubeVideo = $response->getItems()[0];

            $snippet = $youtubeVideo->getSnippet();
            $video->setTitle($snippet->getTitle());
            $video->setThumbnail($snippet->getThumbnails()->getHigh()->getUrl());

            $video->setDescription($snippet->getDescription());

            $statistics = $youtubeVideo->getStatistics();
            $video->setLikeCount((int) $statistics->getLikeCount());
            $video->setViewCount((int) $statistics->getViewCount());
        }
    }

    /**
     * @param Video $video
     */
    public function preRemove(Video $video): void
    {
        $video->getPlayer()->setNbVideo($video->getPlayer()->getNbVideo() - 1);

        $video->getGame()
            ?->setNbVideo(
                $video->getGame()
                    ->getNbVideo() - 1
            );
    }
}
