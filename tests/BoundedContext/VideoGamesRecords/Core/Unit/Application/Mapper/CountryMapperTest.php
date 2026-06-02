<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Unit\Application\Mapper;

use App\BoundedContext\VideoGamesRecords\Core\Application\Mapper\CountryMapper;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Country;
use PHPUnit\Framework\TestCase;

class CountryMapperTest extends TestCase
{
    private CountryMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new CountryMapper();
    }

    private function makeCountry(
        int $id = 1,
        ?string $name = 'France',
        string $iso2 = 'FR',
        string $iso3 = 'FRA',
        string $slug = 'france',
    ): Country {
        $country = $this->createMock(Country::class);
        $country->method('getId')->willReturn($id);
        $country->method('getName')->willReturn($name);
        $country->method('getDefaultName')->willReturn('Default');
        $country->method('getCodeIso2')->willReturn($iso2);
        $country->method('getCodeIso3')->willReturn($iso3);
        $country->method('getSlug')->willReturn($slug);
        return $country;
    }

    public function testToDTOMapsAllFields(): void
    {
        $dto = $this->mapper->toDTO($this->makeCountry());

        $this->assertSame(1, $dto->id);
        $this->assertSame('France', $dto->name);
        $this->assertSame('FR', $dto->iso2);
        $this->assertSame('FRA', $dto->iso3);
        $this->assertSame('france', $dto->slug);
    }

    public function testToDTOFallsBackToDefaultNameWhenNameIsNull(): void
    {
        $dto = $this->mapper->toDTO($this->makeCountry(name: null));

        $this->assertSame('Default', $dto->name);
    }

    public function testToDTOCollectionMapsAllCountries(): void
    {
        $countries = [
            $this->makeCountry(1, 'France', 'FR', 'FRA', 'france'),
            $this->makeCountry(2, 'Germany', 'DE', 'DEU', 'germany'),
        ];

        $dtos = $this->mapper->toDTOCollection($countries);

        $this->assertCount(2, $dtos);
        $this->assertSame(1, $dtos[0]->id);
        $this->assertSame(2, $dtos[1]->id);
    }

    public function testToDTOCollectionReturnsEmptyArrayForEmptyInput(): void
    {
        $this->assertSame([], $this->mapper->toDTOCollection([]));
    }
}
