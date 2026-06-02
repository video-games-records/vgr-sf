<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Video\Unit\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Video\Domain\Entity\Video;
use App\BoundedContext\VideoGamesRecords\Video\Domain\ValueObject\VideoType;
use App\BoundedContext\VideoGamesRecords\Video\Infrastructure\DataProvider\YoutubeProvider;
use App\BoundedContext\VideoGamesRecords\Video\Infrastructure\Doctrine\EventListener\VideoListener;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class VideoListenerTest extends TestCase
{
    private function makeListener(): VideoListener
    {
        return new VideoListener(
            $this->createMock(YoutubeProvider::class),
            $this->createMock(TranslatorInterface::class),
        );
    }

    public function testPrePersistIncrementsPlayerNbVideo(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getNbVideo')->willReturn(2);
        $player->expects($this->once())->method('setNbVideo')->with(3);

        $video = $this->createMock(Video::class);
        $video->method('getPlayer')->willReturn($player);
        $video->method('getGame')->willReturn(null);
        $video->method('getVideoType')->willReturn(VideoType::TWITCH);

        $this->makeListener()->prePersist($video);
    }

    public function testPrePersistIncrementsGameNbVideoWhenGameExists(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getNbVideo')->willReturn(0);

        $game = $this->createMock(Game::class);
        $game->method('getNbVideo')->willReturn(4);
        $game->expects($this->once())->method('setNbVideo')->with(5);

        $video = $this->createMock(Video::class);
        $video->method('getPlayer')->willReturn($player);
        $video->method('getGame')->willReturn($game);
        $video->method('getVideoType')->willReturn(VideoType::TWITCH);

        $this->makeListener()->prePersist($video);
    }

    public function testPrePersistDoesNotFailWhenGameIsNull(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getNbVideo')->willReturn(1);
        $player->expects($this->once())->method('setNbVideo')->with(2);

        $video = $this->createMock(Video::class);
        $video->method('getPlayer')->willReturn($player);
        $video->method('getGame')->willReturn(null);
        $video->method('getVideoType')->willReturn(VideoType::UNKNOWN);

        $this->makeListener()->prePersist($video);
    }

    public function testPreRemoveDecrementsPlayerNbVideo(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getNbVideo')->willReturn(5);
        $player->expects($this->once())->method('setNbVideo')->with(4);

        $video = $this->createMock(Video::class);
        $video->method('getPlayer')->willReturn($player);
        $video->method('getGame')->willReturn(null);

        $this->makeListener()->preRemove($video);
    }

    public function testPreRemoveDecrementsGameNbVideoWhenGameExists(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getNbVideo')->willReturn(1);

        $game = $this->createMock(Game::class);
        $game->method('getNbVideo')->willReturn(3);
        $game->expects($this->once())->method('setNbVideo')->with(2);

        $video = $this->createMock(Video::class);
        $video->method('getPlayer')->willReturn($player);
        $video->method('getGame')->willReturn($game);

        $this->makeListener()->preRemove($video);
    }

    public function testPreRemoveDoesNotFailWhenGameIsNull(): void
    {
        $player = $this->createMock(Player::class);
        $player->method('getNbVideo')->willReturn(1);
        $player->expects($this->once())->method('setNbVideo')->with(0);

        $video = $this->createMock(Video::class);
        $video->method('getPlayer')->willReturn($player);
        $video->method('getGame')->willReturn(null);

        $this->makeListener()->preRemove($video);
    }
}
