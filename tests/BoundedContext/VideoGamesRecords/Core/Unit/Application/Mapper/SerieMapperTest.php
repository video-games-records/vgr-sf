<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\SerieMapper;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\SerieStatus;
use PHPUnit\Framework\TestCase;

class SerieMapperTest extends TestCase
{
    private SerieMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new SerieMapper();
    }

    private function makeSerie(
        int $id = 5,
        string $name = 'Mario',
        ?string $picture = 'mario.png',
        SerieStatus $status = SerieStatus::ACTIVE,
        int $nbChart = 100,
        int $nbGame = 10,
        int $nbPlayer = 50,
        int $nbTeam = 5,
        string $slug = 'mario',
    ): Serie {
        $serie = $this->createMock(Serie::class);
        $serie->method('getId')->willReturn($id);
        $serie->method('getName')->willReturn($name);
        $serie->method('getPicture')->willReturn($picture);
        $serie->method('getStatus')->willReturn($status);
        $serie->method('getNbChart')->willReturn($nbChart);
        $serie->method('getNbGame')->willReturn($nbGame);
        $serie->method('getNbPlayer')->willReturn($nbPlayer);
        $serie->method('getNbTeam')->willReturn($nbTeam);
        $serie->method('getSlug')->willReturn($slug);
        return $serie;
    }

    public function testToDTOMapsAllFields(): void
    {
        $dto = $this->mapper->toDTO($this->makeSerie());

        $this->assertSame(5, $dto->id);
        $this->assertSame('Mario', $dto->name);
        $this->assertSame('mario.png', $dto->picture);
        $this->assertSame('ACTIVE', $dto->status);
        $this->assertSame(100, $dto->nbChart);
        $this->assertSame(10, $dto->nbGame);
        $this->assertSame(50, $dto->nbPlayer);
        $this->assertSame(5, $dto->nbTeam);
        $this->assertSame('mario', $dto->slug);
    }

    public function testToDTOMapsInactiveStatus(): void
    {
        $dto = $this->mapper->toDTO($this->makeSerie(status: SerieStatus::INACTIVE));

        $this->assertSame('INACTIVE', $dto->status);
    }

    public function testToDTOHandlesNullPicture(): void
    {
        $dto = $this->mapper->toDTO($this->makeSerie(picture: null));

        $this->assertNull($dto->picture);
    }
}
