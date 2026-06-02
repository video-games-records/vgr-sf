<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Video\Unit\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Video\Application\DTO\VideoComment\VideoCommentDTO;
use App\BoundedContext\VideoGamesRecords\Video\Application\Mapper\VideoCommentMapper;
use App\BoundedContext\VideoGamesRecords\Video\Domain\Entity\VideoComment;
use PHPUnit\Framework\TestCase;

class VideoCommentMapperTest extends TestCase
{
    public function testToDTOMapsAllFields(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getId')->willReturn(7);
        $player->method('getPseudo')->willReturn('GamerTag');
        $player->method('getSlug')->willReturn('gamertag');

        $createdAt = new \DateTime('2025-03-01 10:00:00');

        $comment = $this->createMock(VideoComment::class);
        $comment->method('getId')->willReturn(99);
        $comment->method('getContent')->willReturn('Great video!');
        $comment->method('getCreatedAt')->willReturn($createdAt);
        $comment->method('getPlayer')->willReturn($player);

        $dto = (new VideoCommentMapper())->toDTO($comment);

        $this->assertInstanceOf(VideoCommentDTO::class, $dto);
        $this->assertSame(99, $dto->id);
        $this->assertSame('Great video!', $dto->content);
        $this->assertSame($createdAt, $dto->createdAt);
        $this->assertSame(7, $dto->player['id']);
        $this->assertSame('GamerTag', $dto->player['pseudo']);
        $this->assertSame('gamertag', $dto->player['slug']);
    }

    public function testToDTOWithNullCreatedAt(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getId')->willReturn(1);
        $player->method('getPseudo')->willReturn('Player');
        $player->method('getSlug')->willReturn('player');

        $comment = $this->createMock(VideoComment::class);
        $comment->method('getId')->willReturn(1);
        $comment->method('getContent')->willReturn('Hello');
        $comment->method('getCreatedAt')->willReturn(null);
        $comment->method('getPlayer')->willReturn($player);

        $dto = (new VideoCommentMapper())->toDTO($comment);

        $this->assertNull($dto->createdAt);
    }
}
