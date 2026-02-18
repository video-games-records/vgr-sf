<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Proof\Application\DTO\Video\VideoDTO;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Video;

class VideoMapper
{
    public function toDTO(Video $video): VideoDTO
    {
        $game = null;
        if ($video->getGame() !== null) {
            $game = [
                'id' => (int) $video->getGame()->getId(),
                'slug' => $video->getGame()->getSlug(),
                'name' => $video->getGame()->getName(),
            ];
        }

        $player = [
            'id' => (int) $video->getPlayer()->getId(),
            'pseudo' => $video->getPlayer()->getPseudo(),
            'slug' => $video->getPlayer()->getSlug(),
        ];

        return new VideoDTO(
            id: (int) $video->getId(),
            type: $video->getType(),
            externalId: $video->getExternalId(),
            url: $video->getUrl(),
            nbComment: $video->getNbComment(),
            slug: $video->getSlug(),
            game: $game,
            createdAt: $video->getCreatedAt(),
            player: $player,
            viewCount: $video->getViewCount(),
            likeCount: $video->getLikeCount(),
            title: $video->getTitle(),
            description: $video->getDescription(),
            thumbnail: $video->getThumbnail(),
        );
    }
}
