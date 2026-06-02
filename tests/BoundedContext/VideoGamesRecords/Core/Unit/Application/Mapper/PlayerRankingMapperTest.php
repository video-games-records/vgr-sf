<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\PlayerRankingMapper;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Country;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerGame;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\PlayerSerie;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use PHPUnit\Framework\TestCase;

class PlayerRankingMapperTest extends TestCase
{
    private PlayerRankingMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new PlayerRankingMapper();
    }

    private function makePlayer(bool $withCountry = false, bool $withTeam = false): Player
    {
        $player = $this->createMock(Player::class);
        $player->method('getId')->willReturn(7);
        $player->method('getPseudo')->willReturn('Gamer');
        $player->method('getSlug')->willReturn('gamer');

        if ($withCountry) {
            $country = $this->createMock(Country::class);
            $country->method('getId')->willReturn(33);
            $country->method('getName')->willReturn('France');
            $country->method('getCodeIso2')->willReturn('FR');
            $player->method('getCountry')->willReturn($country);
        } else {
            $player->method('getCountry')->willReturn(null);
        }

        if ($withTeam) {
            $team = $this->createMock(Team::class);
            $team->method('getId')->willReturn(11);
            $team->method('getName')->willReturn('Dream Team');
            $team->method('getSlug')->willReturn('dream-team');
            $player->method('getTeam')->willReturn($team);
        } else {
            $player->method('getTeam')->willReturn(null);
        }

        return $player;
    }

    private function makePlayerGame(Player $player): PlayerGame
    {
        $pg = $this->createMock(PlayerGame::class);
        $pg->method('getPlayer')->willReturn($player);
        $pg->method('getRankPointChart')->willReturn(2);
        $pg->method('getPointChart')->willReturn(500);
        $pg->method('getNbChart')->willReturn(20);
        $pg->method('getNbChartProven')->willReturn(15);
        $pg->method('getChartRank0')->willReturn(1);
        $pg->method('getChartRank1')->willReturn(3);
        $pg->method('getChartRank2')->willReturn(5);
        $pg->method('getChartRank3')->willReturn(7);
        return $pg;
    }

    private function makePlayerSerie(Player $player): PlayerSerie
    {
        $ps = $this->createMock(PlayerSerie::class);
        $ps->method('getPlayer')->willReturn($player);
        $ps->method('getRankPointChart')->willReturn(4);
        $ps->method('getPointChart')->willReturn(250);
        $ps->method('getNbChart')->willReturn(10);
        $ps->method('getNbChartProven')->willReturn(8);
        $ps->method('getChartRank0')->willReturn(0);
        $ps->method('getChartRank1')->willReturn(2);
        $ps->method('getChartRank2')->willReturn(3);
        $ps->method('getChartRank3')->willReturn(4);
        return $ps;
    }

    public function testFromPlayerGameMapsScalarFields(): void
    {
        $player = $this->makePlayer();
        $dto = $this->mapper->fromPlayerGame($this->makePlayerGame($player));

        $this->assertSame(7, $dto->id);
        $this->assertSame(2, $dto->rank);
        $this->assertSame(500, $dto->pointChart);
        $this->assertSame(20, $dto->nbChart);
        $this->assertSame(15, $dto->nbChartProven);
        $this->assertSame(1, $dto->platinum);
        $this->assertSame(3, $dto->gold);
        $this->assertSame(5, $dto->silver);
        $this->assertSame(7, $dto->bronze);
    }

    public function testFromPlayerGameMapsPlayerData(): void
    {
        $player = $this->makePlayer();
        $dto = $this->mapper->fromPlayerGame($this->makePlayerGame($player));

        $this->assertSame(7, $dto->player['id']);
        $this->assertSame('Gamer', $dto->player['pseudo']);
        $this->assertSame('gamer', $dto->player['slug']);
    }

    public function testFromPlayerGameWithNoCountryAndNoTeam(): void
    {
        $player = $this->makePlayer(withCountry: false, withTeam: false);
        $dto = $this->mapper->fromPlayerGame($this->makePlayerGame($player));

        $this->assertNull($dto->player['country']);
        $this->assertNull($dto->player['team']);
    }

    public function testFromPlayerGameWithCountryMapsCountryData(): void
    {
        $player = $this->makePlayer(withCountry: true);
        $dto = $this->mapper->fromPlayerGame($this->makePlayerGame($player));

        $this->assertNotNull($dto->player['country']);
        $this->assertSame(33, $dto->player['country']['id']);
        $this->assertSame('France', $dto->player['country']['name']);
        $this->assertSame('FR', $dto->player['country']['codeIso2']);
    }

    public function testFromPlayerGameWithTeamMapsTeamData(): void
    {
        $player = $this->makePlayer(withTeam: true);
        $dto = $this->mapper->fromPlayerGame($this->makePlayerGame($player));

        $this->assertNotNull($dto->player['team']);
        $this->assertSame(11, $dto->player['team']['id']);
        $this->assertSame('Dream Team', $dto->player['team']['name']);
        $this->assertSame('dream-team', $dto->player['team']['slug']);
    }

    public function testFromPlayerSerieMapsScalarFields(): void
    {
        $player = $this->makePlayer();
        $dto = $this->mapper->fromPlayerSerie($this->makePlayerSerie($player));

        $this->assertSame(7, $dto->id);
        $this->assertSame(4, $dto->rank);
        $this->assertSame(250, $dto->pointChart);
        $this->assertSame(10, $dto->nbChart);
        $this->assertSame(8, $dto->nbChartProven);
        $this->assertSame(0, $dto->platinum);
        $this->assertSame(2, $dto->gold);
        $this->assertSame(3, $dto->silver);
        $this->assertSame(4, $dto->bronze);
    }
}
