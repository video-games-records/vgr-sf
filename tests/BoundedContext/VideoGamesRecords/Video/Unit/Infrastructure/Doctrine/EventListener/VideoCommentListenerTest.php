<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Video\Unit\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\VideoGamesRecords\Video\Domain\Entity\Video;
use App\BoundedContext\VideoGamesRecords\Video\Domain\Entity\VideoComment;
use App\BoundedContext\VideoGamesRecords\Video\Infrastructure\Doctrine\EventListener\VideoCommentListener;
use PHPUnit\Framework\TestCase;

class VideoCommentListenerTest extends TestCase
{
    public function testPrePersistIncrementsNbComment(): void
    {
        $video = $this->createMock(Video::class);
        $video->method('getNbComment')->willReturn(5);
        $video->expects($this->once())->method('setNbComment')->with(6);

        $comment = $this->createMock(VideoComment::class);
        $comment->method('getVideo')->willReturn($video);

        (new VideoCommentListener())->prePersist($comment);
    }

    public function testPreRemoveDecrementsNbComment(): void
    {
        $video = $this->createMock(Video::class);
        $video->method('getNbComment')->willReturn(3);
        $video->expects($this->once())->method('setNbComment')->with(2);

        $comment = $this->createMock(VideoComment::class);
        $comment->method('getVideo')->willReturn($video);

        (new VideoCommentListener())->preRemove($comment);
    }

    public function testPrePersistStartingFromZeroIncrementsToOne(): void
    {
        $video = $this->createMock(Video::class);
        $video->method('getNbComment')->willReturn(0);
        $video->expects($this->once())->method('setNbComment')->with(1);

        $comment = $this->createMock(VideoComment::class);
        $comment->method('getVideo')->willReturn($video);

        (new VideoCommentListener())->prePersist($comment);
    }
}
