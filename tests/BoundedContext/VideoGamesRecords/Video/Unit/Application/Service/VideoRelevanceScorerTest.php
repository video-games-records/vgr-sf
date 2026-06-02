<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Video\Unit\Application\Service;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Video\Application\Service\VideoRelevanceScorer;
use App\BoundedContext\VideoGamesRecords\Video\Domain\Entity\Video;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class VideoRelevanceScorerTest extends TestCase
{
    private VideoRelevanceScorer $scorer;

    protected function setUp(): void
    {
        $this->scorer = new VideoRelevanceScorer();
    }

    private function makeVideo(
        ?int $id = null,
        ?Game $game = null,
        int $viewCount = 0,
        int $likeCount = 0,
        ?\DateTime $createdAt = null,
    ): Video&MockObject {
        $video = $this->createMock(Video::class);
        $video->method('getId')->willReturn($id);
        $video->method('getGame')->willReturn($game);
        $video->method('getViewCount')->willReturn($viewCount);
        $video->method('getLikeCount')->willReturn($likeCount);
        $video->method('getCreatedAt')->willReturn($createdAt ?? new \DateTime('-100 days'));
        return $video;
    }

    private function makeGame(?int $id = null, ?Game $serie = null): Game&MockObject
    {
        $game = $this->createMock(Game::class);
        $game->method('getId')->willReturn($id);
        $game->method('getSerie')->willReturn($serie);
        $game->method('getIgdbGame')->willReturn(null);
        $game->method('getPlatforms')->willReturn(new \Doctrine\Common\Collections\ArrayCollection());
        return $game;
    }

    // ------------------------------------------------------------------
    // calculateScore — basic cases
    // ------------------------------------------------------------------

    public function testCalculateScoreReturnsHigherScoreForSameGame(): void
    {
        $game = $this->makeGame(42);
        $source = $this->makeVideo(1, $game);
        $sameGame = $this->makeVideo(2, $game);
        $otherGame = $this->makeVideo(3, $this->makeGame(99));

        $scoreSame = $this->scorer->calculateScore($source, $sameGame);
        $scoreOther = $this->scorer->calculateScore($source, $otherGame);

        $this->assertGreaterThan($scoreOther, $scoreSame);
    }

    public function testCalculateScoreIsPositiveOrZero(): void
    {
        $source = $this->makeVideo(1);
        $candidate = $this->makeVideo(2);

        $score = $this->scorer->calculateScore($source, $candidate);
        $this->assertGreaterThanOrEqual(0, $score);
    }

    public function testCalculateScoreWithPopularRecentVideo(): void
    {
        $source = $this->makeVideo(1);
        $popularRecent = $this->makeVideo(2, null, 2000, 0, new \DateTime('-5 days'));
        $oldUnpopular = $this->makeVideo(3, null, 0, 0, new \DateTime('-400 days'));

        $scoreRecent = $this->scorer->calculateScore($source, $popularRecent);
        $scoreOld = $this->scorer->calculateScore($source, $oldUnpopular);

        $this->assertGreaterThan($scoreOld, $scoreRecent);
    }

    // ------------------------------------------------------------------
    // rankVideos
    // ------------------------------------------------------------------

    public function testRankVideosReturnsAllVideos(): void
    {
        $source = $this->makeVideo(1);
        $v2 = $this->makeVideo(2);
        $v3 = $this->makeVideo(3);

        $result = $this->scorer->rankVideos($source, [$v2, $v3]);
        $this->assertCount(2, $result);
    }

    public function testRankVideosReturnsEmptyArrayForEmptyInput(): void
    {
        $source = $this->makeVideo(1);
        $result = $this->scorer->rankVideos($source, []);
        $this->assertSame([], $result);
    }

    public function testRankVideosSortsHighestScoreFirst(): void
    {
        $game = $this->makeGame(10);
        $source = $this->makeVideo(1, $game);
        $sameGame = $this->makeVideo(2, $game);
        $noGame = $this->makeVideo(3, null, 0, 0, new \DateTime('-400 days'));

        $ranked = $this->scorer->rankVideos($source, [$noGame, $sameGame]);

        $this->assertSame($sameGame, $ranked[0]);
        $this->assertSame($noGame, $ranked[1]);
    }

    // ------------------------------------------------------------------
    // rankVideosWithScores
    // ------------------------------------------------------------------

    public function testRankVideosWithScoresReturnsScoreKeys(): void
    {
        $source = $this->makeVideo(1);
        $candidate = $this->makeVideo(2);

        $result = $this->scorer->rankVideosWithScores($source, [$candidate]);

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('video', $result[0]);
        $this->assertArrayHasKey('score', $result[0]);
        $this->assertArrayHasKey('debug', $result[0]);
    }

    public function testRankVideosWithScoresIsSortedByScoreDesc(): void
    {
        $game = $this->makeGame(7);
        $source = $this->makeVideo(1, $game);
        $highScore = $this->makeVideo(2, $game);
        $lowScore = $this->makeVideo(3, null, 0, 0, new \DateTime('-400 days'));

        $result = $this->scorer->rankVideosWithScores($source, [$lowScore, $highScore]);

        $this->assertSame($highScore, $result[0]['video']);
    }
}
