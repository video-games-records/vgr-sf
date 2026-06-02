<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\DTO\Country\CountryDTO;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\CountryMapper;
use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\PlayerMapper;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Country;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerStatusEnum;
use DateTime;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class PlayerMapperTest extends TestCase
{
    private PlayerMapper $mapper;
    private CountryMapper&MockObject $countryMapper;

    protected function setUp(): void
    {
        $this->countryMapper = $this->createMock(CountryMapper::class);
        $this->mapper = new PlayerMapper($this->countryMapper, 'https://cdn.example.com');
    }

    private function makePlayer(bool $withCountry = false): Player
    {
        $player = $this->createMock(Player::class);
        $player->method('getId')->willReturn(1);
        $player->method('getPseudo')->willReturn('Legend');
        $player->method('getSlug')->willReturn('legend');
        $player->method('getStatus')->willReturn(PlayerStatusEnum::MEMBER);
        $player->method('getNbConnexion')->willReturn(99);
        $player->method('getHasDonate')->willReturn(true);
        $player->method('getLastLogin')->willReturn(new DateTime('2024-06-01'));
        $player->method('getCreatedAt')->willReturn(new DateTime('2020-01-01'));
        $player->method('getPresentation')->willReturn('Hello World');
        $player->method('getCollection')->willReturn('My games');
        $player->method('getBirthDate')->willReturn(null);
        $player->method('getAvatar')->willReturn('avatar.png');

        // Stats from traits
        $player->method('getPointGame')->willReturn(100);
        $player->method('getPointChart')->willReturn(200);
        $player->method('getPointBadge')->willReturn(50);
        $player->method('getNbGame')->willReturn(10);
        $player->method('getNbChart')->willReturn(30);
        $player->method('getNbVideo')->willReturn(5);
        $player->method('getNbMasterBadge')->willReturn(3);
        $player->method('getNbChartProven')->willReturn(20);
        $player->method('getNbChartMax')->willReturn(40);
        $player->method('getChartRank0')->willReturn(1);
        $player->method('getChartRank1')->willReturn(2);
        $player->method('getChartRank2')->willReturn(3);
        $player->method('getChartRank3')->willReturn(4);
        $player->method('getChartRank4')->willReturn(0);
        $player->method('getChartRank5')->willReturn(0);
        $player->method('getGameRank0')->willReturn(1);
        $player->method('getGameRank1')->willReturn(0);
        $player->method('getGameRank2')->willReturn(0);
        $player->method('getGameRank3')->willReturn(0);
        $player->method('getRankCup')->willReturn(5);
        $player->method('getRankMedal')->willReturn(6);
        $player->method('getRankBadge')->willReturn(7);
        $player->method('getRankPointChart')->willReturn(8);
        $player->method('getRankPointGame')->willReturn(9);
        $player->method('getRankCountry')->willReturn(2);
        $player->method('getRankProof')->willReturn(3);
        $player->method('getAverageChartRank')->willReturn(12.5);
        $player->method('getAverageGameRank')->willReturn(8.3);

        if ($withCountry) {
            $country = $this->createMock(Country::class);
            $player->method('getCountry')->willReturn($country);
            $this->countryMapper
                ->method('toDTO')
                ->with($country)
                ->willReturn(new CountryDTO(33, 'France', 'FR', 'FRA', 'france'));
        } else {
            $player->method('getCountry')->willReturn(null);
        }

        return $player;
    }

    public function testToDTOMapsScalarFields(): void
    {
        $dto = $this->mapper->toDTO($this->makePlayer());

        $this->assertSame(1, $dto->id);
        $this->assertSame('Legend', $dto->pseudo);
        $this->assertSame('legend', $dto->slug);
        $this->assertSame('MEMBER', $dto->status);
        $this->assertSame(99, $dto->nbConnexion);
        $this->assertTrue($dto->hasDonate);
        $this->assertSame('Hello World', $dto->presentation);
        $this->assertSame('My games', $dto->collection);
    }

    public function testToDTOBuildsAvatarUrl(): void
    {
        $dto = $this->mapper->toDTO($this->makePlayer());

        $this->assertSame('https://cdn.example.com/user/avatar.png', $dto->avatarUrl);
    }

    public function testToDTOWithNoCountryReturnsNull(): void
    {
        $dto = $this->mapper->toDTO($this->makePlayer(withCountry: false));

        $this->assertNull($dto->country);
    }

    public function testToDTOWithCountryDelegatesToCountryMapper(): void
    {
        $dto = $this->mapper->toDTO($this->makePlayer(withCountry: true));

        $this->assertNotNull($dto->country);
        $this->assertSame(33, $dto->country->id);
        $this->assertSame('France', $dto->country->name);
    }

    public function testToDTOMapsStats(): void
    {
        $dto = $this->mapper->toDTO($this->makePlayer());

        $this->assertSame(100, $dto->stats->pointGame);
        $this->assertSame(200, $dto->stats->pointChart);
        $this->assertSame(50, $dto->stats->pointBadge);
        $this->assertSame(10, $dto->stats->nbGame);
        $this->assertSame(30, $dto->stats->nbChart);
        $this->assertSame(5, $dto->stats->nbVideo);
        $this->assertSame(3, $dto->stats->nbMasterBadge);
        $this->assertSame(20, $dto->stats->nbChartProven);
        $this->assertSame(40, $dto->stats->nbChartMax);
        $this->assertSame(1, $dto->stats->chartRank0);
        $this->assertSame(1, $dto->stats->gameRank0);
        $this->assertSame(5, $dto->stats->rankCup);
        $this->assertSame(6, $dto->stats->rankMedal);
        $this->assertSame(7, $dto->stats->rankBadge);
    }
}
