<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Proof\Application\DTO\VideoComment\VideoCommentDTO;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\VideoComment;

class VideoCommentMapper
{
    public function toDTO(VideoComment $comment): VideoCommentDTO
    {
        return new VideoCommentDTO(
            id: (int) $comment->getId(),
            content: $comment->getContent(),
            createdAt: $comment->getCreatedAt(),
            player: [
                'id' => (int) $comment->getPlayer()->getId(),
                'pseudo' => $comment->getPlayer()->getPseudo(),
                'slug' => $comment->getPlayer()->getSlug(),
            ],
        );
    }
}
