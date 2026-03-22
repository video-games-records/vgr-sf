<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Web;

use App\Tests\BoundedContext\VideoGamesRecords\Core\Factory\CountryFactory;

class CountryTest extends AbstractWebFunctionalTestCase
{
    public function testCountryShow(): void
    {
        $country = CountryFactory::new()->france()->create();

        $url = sprintf('/en/country/%d-%s', $country->getId(), $country->getSlug());
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public function testCountryNotFound(): void
    {
        $this->client->request('GET', '/en/country/99999-nonexistent-country');

        $this->assertResponseStatusCodeSame(404);
    }
}
