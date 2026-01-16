<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Application\Handler;

use Doctrine\ORM\EntityManagerInterface;
use App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\DataProvider\YoutubeProvider;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Video;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\VideoType;

class YoutubeDataHandler
{
    protected EntityManagerInterface $em;
    protected YoutubeProvider $youtubeProvider;

    public function __construct(EntityManagerInterface $em, YoutubeProvider $youtubeProvider)
    {
        $this->em = $em;
        $this->youtubeProvider = $youtubeProvider;
    }

    /**
     * @param Video $video
     * @return void
     */
    public function process(Video $video): void
    {
        if ($video->getVideoType()->getValue() !== VideoType::YOUTUBE) {
            throw new \InvalidArgumentException();
        }

        try {
            $response = $this->youtubeProvider->getVideo($video->getExternalId());
            if (count($response->getItems()) > 0) {
                $youtubeVideo = $response->getItems()[0];

                $snippet = $youtubeVideo->getSnippet();
                $video->setTitle($snippet->getTitle());
                $video->setThumbnail($snippet->getThumbnails()->getHigh()->getUrl());

                $video->setDescription($snippet->getDescription());

                $statistics = $youtubeVideo->getStatistics();
                $video->setLikeCount((int) $statistics->getLikeCount());
                $video->setViewCount((int) $statistics->getViewCount());
            } else {
                $video->setIsActive(false);
            }
            $this->em->flush();
        } catch (\Exception $e) {
        }
    }
}
