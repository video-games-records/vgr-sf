<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Api;

use App\BoundedContext\VideoGamesRecords\Core\Tests\Factory\GameFactory;

class GameTest extends AbstractFunctionalTestCase
{
    public function testGetItem(): void
    {
        // Create a test game
        $game = GameFactory::createOne([
            'libGameEn' => 'Super Mario Odyssey',
            'libGameFr' => 'Super Mario Odyssey',
        ]);

        $response = $this->apiClient->request('GET', '/api/games/' . $game->getId());

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $data = $response->toArray();

        $this->assertArrayHasKey('@context', $data);
        $this->assertArrayHasKey('@id', $data);
        $this->assertArrayHasKey('@type', $data);
        $this->assertEquals('GameDTO', $data['@type']);

        $this->assertArrayHasKey('id', $data);
        $this->assertIsInt($data['id']);
        $this->assertEquals($game->getId(), $data['id']);

        $this->assertArrayHasKey('name', $data);
        $this->assertIsString($data['name']);

        $this->assertArrayHasKey('status', $data);
        $this->assertIsString($data['status']);

        $this->assertArrayHasKey('slug', $data);
        $this->assertIsString($data['slug']);
    }

    public function testGetItemNotFound(): void
    {
        $this->apiClient->request('GET', '/api/games/999999');

        $this->assertResponseStatusCodeSame(404);
    }
}
