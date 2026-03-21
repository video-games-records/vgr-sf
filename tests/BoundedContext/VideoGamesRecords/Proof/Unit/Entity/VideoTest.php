<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Proof\Unit\Entity;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Tag;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Video;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\VideoType;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VideoTest extends TestCase
{
    private Video $video;

    protected function setUp(): void
    {
        $this->video = new Video();
    }

    // ------------------------------------------------------------------
    // Constructor
    // ------------------------------------------------------------------

    public function testConstructorInitializesEmptyCollections(): void
    {
        $video = new Video();

        $this->assertInstanceOf(Collection::class, $video->getComments());
        $this->assertCount(0, $video->getComments());
        $this->assertInstanceOf(Collection::class, $video->getTags());
        $this->assertCount(0, $video->getTags());
    }

    // ------------------------------------------------------------------
    // Basic properties — defaults
    // ------------------------------------------------------------------

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->video->getId());
    }

    public function testTypeDefaultsToYoutube(): void
    {
        $this->assertSame(VideoType::YOUTUBE, $this->video->getType());
    }

    public function testNbCommentDefaultsToZero(): void
    {
        $this->assertSame(0, $this->video->getNbComment());
    }

    public function testIsActiveDefaultsToTrue(): void
    {
        $this->assertTrue($this->video->getIsActive());
    }

    public function testViewCountDefaultsToZero(): void
    {
        $this->assertSame(0, $this->video->getViewCount());
    }

    public function testLikeCountDefaultsToZero(): void
    {
        $this->assertSame(0, $this->video->getLikeCount());
    }

    public function testTitleDefaultsToNull(): void
    {
        $this->assertNull($this->video->getTitle());
    }

    public function testDescriptionDefaultsToNull(): void
    {
        $this->assertNull($this->video->getDescription());
    }

    public function testThumbnailDefaultsToNull(): void
    {
        $this->assertNull($this->video->getThumbnail());
    }

    public function testGameDefaultsToNull(): void
    {
        $this->assertNull($this->video->getGame());
    }

    // ------------------------------------------------------------------
    // Getters / setters
    // ------------------------------------------------------------------

    public function testSetAndGetId(): void
    {
        $result = $this->video->setId(5);
        $this->assertSame(5, $this->video->getId());
        $this->assertSame($this->video, $result);
    }

    public function testSetAndGetType(): void
    {
        $result = $this->video->setType(VideoType::TWITCH);
        $this->assertSame(VideoType::TWITCH, $this->video->getType());
        $this->assertSame($this->video, $result);
    }

    public function testGetVideoTypeReturnsValueObject(): void
    {
        $this->video->setType(VideoType::YOUTUBE);
        $videoType = $this->video->getVideoType();
        $this->assertInstanceOf(VideoType::class, $videoType);
        $this->assertSame(VideoType::YOUTUBE, $videoType->getValue());
    }

    public function testSetAndGetExternalId(): void
    {
        $result = $this->video->setExternalId('dQw4w9WgXcQ');
        $this->assertSame('dQw4w9WgXcQ', $this->video->getExternalId());
        $this->assertSame($this->video, $result);
    }

    public function testSetAndGetNbComment(): void
    {
        $result = $this->video->setNbComment(42);
        $this->assertSame(42, $this->video->getNbComment());
        $this->assertSame($this->video, $result);
    }

    public function testSetAndGetIsActive(): void
    {
        $result = $this->video->setIsActive(false);
        $this->assertFalse($this->video->getIsActive());
        $this->assertSame($this->video, $result);
    }

    public function testSetAndGetViewCount(): void
    {
        $result = $this->video->setViewCount(1000);
        $this->assertSame(1000, $this->video->getViewCount());
        $this->assertSame($this->video, $result);
    }

    public function testSetAndGetLikeCount(): void
    {
        $result = $this->video->setLikeCount(150);
        $this->assertSame(150, $this->video->getLikeCount());
        $this->assertSame($this->video, $result);
    }

    public function testSetAndGetTitle(): void
    {
        $result = $this->video->setTitle('My World Record');
        $this->assertSame('My World Record', $this->video->getTitle());
        $this->assertSame($this->video, $result);
    }

    public function testSetTitleToNull(): void
    {
        $this->video->setTitle('Something');
        $this->video->setTitle(null);
        $this->assertNull($this->video->getTitle());
    }

    public function testSetAndGetDescription(): void
    {
        $result = $this->video->setDescription('This is my run.');
        $this->assertSame('This is my run.', $this->video->getDescription());
        $this->assertSame($this->video, $result);
    }

    public function testSetDescriptionToNull(): void
    {
        $this->video->setDescription('text');
        $this->video->setDescription(null);
        $this->assertNull($this->video->getDescription());
    }

    public function testSetAndGetThumbnail(): void
    {
        $result = $this->video->setThumbnail('https://img.youtube.com/vi/xxx/0.jpg');
        $this->assertSame('https://img.youtube.com/vi/xxx/0.jpg', $this->video->getThumbnail());
        $this->assertSame($this->video, $result);
    }

    public function testSetThumbnailToNull(): void
    {
        $this->video->setThumbnail('img.jpg');
        $this->video->setThumbnail(null);
        $this->assertNull($this->video->getThumbnail());
    }

    // ------------------------------------------------------------------
    // Player relation (from PlayerTrait)
    // ------------------------------------------------------------------

    public function testSetAndGetPlayer(): void
    {
        $player = $this->createMock(Player::class);
        $result = $this->video->setPlayer($player);
        $this->assertSame($player, $this->video->getPlayer());
        $this->assertSame($this->video, $result);
    }

    // ------------------------------------------------------------------
    // Game relation
    // ------------------------------------------------------------------

    public function testSetAndGetGame(): void
    {
        $game = $this->createMock(Game::class);
        $result = $this->video->setGame($game);
        $this->assertSame($game, $this->video->getGame());
        $this->assertSame($this->video, $result);
    }

    public function testSetGameToNull(): void
    {
        $game = $this->createMock(Game::class);
        $this->video->setGame($game);
        $this->video->setGame(null);
        $this->assertNull($this->video->getGame());
    }

    // ------------------------------------------------------------------
    // majTypeAndVideoId — YouTube full URL
    // ------------------------------------------------------------------

    public function testSetUrlDetectsYoutubeWatchUrl(): void
    {
        $this->video->setUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
        $this->assertSame(VideoType::YOUTUBE, $this->video->getType());
        $this->assertSame('dQw4w9WgXcQ', $this->video->getExternalId());
    }

    public function testSetUrlDetectsYoutubeShortUrl(): void
    {
        $this->video->setUrl('https://youtu.be/dQw4w9WgXcQ');
        $this->assertSame(VideoType::YOUTUBE, $this->video->getType());
        $this->assertSame('dQw4w9WgXcQ', $this->video->getExternalId());
    }

    public function testSetUrlDetectsTwitchUrl(): void
    {
        $this->video->setUrl('https://www.twitch.tv/videos/123456789');
        $this->assertSame(VideoType::TWITCH, $this->video->getType());
        $this->assertSame('123456789', $this->video->getExternalId());
    }

    public function testSetUrlSetsUnknownTypeForOtherUrls(): void
    {
        $this->video->setUrl('https://example.com/video/xyz');
        $this->assertSame(VideoType::UNKNOWN, $this->video->getType());
    }

    // ------------------------------------------------------------------
    // getSluggableFields
    // ------------------------------------------------------------------

    public function testGetSluggableFields(): void
    {
        $this->assertSame(['title'], $this->video->getSluggableFields());
    }

    // ------------------------------------------------------------------
    // Tags collection
    // ------------------------------------------------------------------

    public function testAddTag(): void
    {
        $tag = new Tag();
        $tag->setName('NoGlitch');

        $result = $this->video->addTag($tag);

        $this->assertCount(1, $this->video->getTags());
        $this->assertTrue($this->video->getTags()->contains($tag));
        $this->assertSame($this->video, $result);
    }

    public function testAddTagDoesNotDuplicate(): void
    {
        $tag = new Tag();
        $tag->setName('Speedrun');

        $this->video->addTag($tag);
        $this->video->addTag($tag);

        $this->assertCount(1, $this->video->getTags());
    }

    public function testRemoveTag(): void
    {
        $tag = new Tag();
        $tag->setName('Any%');

        $this->video->addTag($tag);
        $result = $this->video->removeTag($tag);

        $this->assertCount(0, $this->video->getTags());
        $this->assertFalse($this->video->getTags()->contains($tag));
        $this->assertSame($this->video, $result);
    }

    public function testRemoveNonExistentTagDoesNothing(): void
    {
        $tag = new Tag();
        $tag->setName('Ghost');

        $this->video->removeTag($tag);

        $this->assertCount(0, $this->video->getTags());
    }

    // ------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------

    public function testToStringWithNullId(): void
    {
        $this->assertSame('Video []', (string) $this->video);
    }

    public function testToStringWithId(): void
    {
        $this->video->setId(77);
        $this->assertSame('Video [77]', (string) $this->video);
    }
}
