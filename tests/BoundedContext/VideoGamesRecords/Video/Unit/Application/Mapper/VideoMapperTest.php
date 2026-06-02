<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Video\Unit\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Video\Application\DTO\Video\VideoDTO;
use App\BoundedContext\VideoGamesRecords\Video\Application\Mapper\VideoMapper;
use App\BoundedContext\VideoGamesRecords\Video\Domain\Entity\Video;
use PHPUnit\Framework\TestCase;

class VideoMapperTest extends TestCase
{
    private function makePlayer(): Player
    {
        $player = $this->createMock(Player::class);
        $player->method('getId')->willReturn(10);
        $player->method('getPseudo')->willReturn('PlayerOne');
        $player->method('getSlug')->willReturn('playerone');
        return $player;
    }

    private function makeVideo(?Game $game = null): Video
    {
        $video = $this->createMock(Video::class);
        $video->method('getId')->willReturn(42);
        $video->method('getType')->willReturn('Youtube');
        $video->method('getExternalId')->willReturn('abc123');
        $video->method('getUrl')->willReturn('https://youtube.com/watch?v=abc123');
        $video->method('getNbComment')->willReturn(7);
        $video->method('getSlug')->willReturn('my-great-video');
        $video->method('getGame')->willReturn($game);
        $video->method('getCreatedAt')->willReturn(new \DateTime('2024-01-15'));
        $video->method('getPlayer')->willReturn($this->makePlayer());
        $video->method('getViewCount')->willReturn(1500);
        $video->method('getLikeCount')->willReturn(80);
        $video->method('getTitle')->willReturn('My Great Video');
        $video->method('getDescription')->willReturn('A description');
        $video->method('getThumbnail')->willReturn('https://img.youtube.com/vi/abc123/hqdefault.jpg');
        return $video;
    }

    public function testToDTOMapsAllFieldsWithoutGame(): void
    {
        $mapper = new VideoMapper();
        $dto = $mapper->toDTO($this->makeVideo(null));

        $this->assertInstanceOf(VideoDTO::class, $dto);
        $this->assertSame(42, $dto->id);
        $this->assertSame('Youtube', $dto->type);
        $this->assertSame('abc123', $dto->externalId);
        $this->assertSame('https://youtube.com/watch?v=abc123', $dto->url);
        $this->assertSame(7, $dto->nbComment);
        $this->assertSame('my-great-video', $dto->slug);
        $this->assertNull($dto->game);
        $this->assertSame(1500, $dto->viewCount);
        $this->assertSame(80, $dto->likeCount);
        $this->assertSame('My Great Video', $dto->title);
        $this->assertSame('A description', $dto->description);
        $this->assertSame('https://img.youtube.com/vi/abc123/hqdefault.jpg', $dto->thumbnail);
    }

    public function testToDTOMapsPlayerArray(): void
    {
        $mapper = new VideoMapper();
        $dto = $mapper->toDTO($this->makeVideo(null));

        $this->assertSame(10, $dto->player['id']);
        $this->assertSame('PlayerOne', $dto->player['pseudo']);
        $this->assertSame('playerone', $dto->player['slug']);
    }

    public function testToDTOMapsGameArrayWhenGameExists(): void
    {
        $game = $this->createMock(Game::class);
        $game->method('getId')->willReturn(5);
        $game->method('getSlug')->willReturn('super-mario');
        $game->method('getName')->willReturn('Super Mario');

        $mapper = new VideoMapper();
        $dto = $mapper->toDTO($this->makeVideo($game));

        $this->assertNotNull($dto->game);
        $this->assertSame(5, $dto->game['id']);
        $this->assertSame('super-mario', $dto->game['slug']);
        $this->assertSame('Super Mario', $dto->game['name']);
    }

    public function testToDTOReturnsDTOInstanceForEachCall(): void
    {
        $mapper = new VideoMapper();
        $dto1 = $mapper->toDTO($this->makeVideo());
        $dto2 = $mapper->toDTO($this->makeVideo());

        $this->assertNotSame($dto1, $dto2);
    }
}
