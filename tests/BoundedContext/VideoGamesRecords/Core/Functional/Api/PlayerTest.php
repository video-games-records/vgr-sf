<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Api;

use App\Tests\BoundedContext\VideoGamesRecords\Core\Factory\PlayerFactory;

class PlayerTest extends AbstractFunctionalTestCase
{
    public function testGetItem(): void
    {
        // Create a test player
        $player = PlayerFactory::createOne([
            'pseudo' => 'TestPlayer',
        ]);

        $response = $this->apiClient->request('GET', '/api/players/' . $player->getId());

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $data = $response->toArray();
        $this->assertArrayHasKey('@type', $data);
        $this->assertEquals('PlayerDTO', $data['@type']);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($player->getId(), $data['id']);
        $this->assertArrayHasKey('pseudo', $data);
        $this->assertEquals('TestPlayer', $data['pseudo']);
    }

    public function testGetItemNotFound(): void
    {
        $this->apiClient->request('GET', '/api/players/99999');

        $this->assertResponseStatusCodeSame(404);
    }
}
