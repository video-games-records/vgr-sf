<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Proof\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Video;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\VideoComment;
use PHPUnit\Framework\TestCase;

class VideoCommentTest extends TestCase
{
    private VideoComment $comment;

    protected function setUp(): void
    {
        $this->comment = new VideoComment();
    }

    // ------------------------------------------------------------------
    // Basic properties — defaults
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->comment->getId());
    }

    // ------------------------------------------------------------------
    // Getters / setters
    // ------------------------------------------------------------------

    public function testSetAndGetId(): void
    {
        $result = $this->comment->setId(12);
        $this->assertSame(12, $this->comment->getId());
        $this->assertSame($this->comment, $result);
    }

    public function testSetAndGetContent(): void
    {
        $result = $this->comment->setContent('Great video!');
        $this->assertSame('Great video!', $this->comment->getContent());
        $this->assertSame($this->comment, $result);
    }

    // ------------------------------------------------------------------
    // Video relation
    // ------------------------------------------------------------------

    public function testSetAndGetVideo(): void
    {
        $video = $this->createMock(Video::class);
        $result = $this->comment->setVideo($video);
        $this->assertSame($video, $this->comment->getVideo());
        $this->assertSame($this->comment, $result);
    }

    // ------------------------------------------------------------------
    // Player relation (from PlayerTrait)
    // ------------------------------------------------------------------

    public function testSetAndGetPlayer(): void
    {
        $player = $this->createMock(Player::class);
        $result = $this->comment->setPlayer($player);
        $this->assertSame($player, $this->comment->getPlayer());
        $this->assertSame($this->comment, $result);
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testToStringWithNullId(): void
    {
        $this->assertSame('comment []', (string) $this->comment);
    }

    public function testToStringWithId(): void
    {
        $this->comment->setId(8);
        $this->assertSame('comment [8]', (string) $this->comment);
    }
}
