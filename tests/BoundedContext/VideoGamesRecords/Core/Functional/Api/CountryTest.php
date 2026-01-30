<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Api;

use App\BoundedContext\VideoGamesRecords\Core\Tests\Factory\CountryFactory;

class CountryTest extends AbstractFunctionalTestCase
{
    public function testGetCountryItem(): void
    {
        // Create a test country using factory method
        $country = CountryFactory::new()->france()->create();

        $response = $this->apiClient->request('GET', '/api/countries/' . $country->getId());

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $data = $response->toArray();
        $this->assertArrayHasKey('@type', $data);
        $this->assertEquals('CountryDTO', $data['@type']);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('iso2', $data);
        $this->assertEquals('FR', $data['iso2']);
        $this->assertArrayHasKey('iso3', $data);
        $this->assertEquals('FRA', $data['iso3']);
    }

    public function testGetCountryItemNotFound(): void
    {
        $this->apiClient->request('GET', '/api/countries/99999');

        $this->assertResponseStatusCodeSame(404);
    }
}
