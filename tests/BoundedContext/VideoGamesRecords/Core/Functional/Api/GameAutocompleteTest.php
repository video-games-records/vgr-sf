<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Functional\Api;

use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\GameStatus;
use App\Tests\BoundedContext\VideoGamesRecords\Core\Factory\GameFactory;

class GameAutocompleteTest extends AbstractFunctionalTestCase
{
    public function testAutocompleteExcludesInactiveGames(): void
    {
        GameFactory::createOne([
            'libGameEn' => 'Autocomplete Active Game',
            'status' => GameStatus::ACTIVE->value,
        ]);
        GameFactory::createOne([
            'libGameEn' => 'Autocomplete Inactive Game',
            'status' => GameStatus::INACTIVE->value,
        ]);
        GameFactory::createOne([
            'libGameEn' => 'Autocomplete Draft Game',
            'status' => GameStatus::ADD_SCORE->value,
        ]);

        $response = $this->apiClient->request(
            'GET',
            '/api/games/autocomplete?' . http_build_query(['query' => 'Autocomplete'])
        );

        $this->assertResponseIsSuccessful();
        $names = array_column($response->toArray(), 'text');

        $this->assertContains('Autocomplete Active Game', $names);
        $this->assertNotContains('Autocomplete Inactive Game', $names);
        $this->assertNotContains('Autocomplete Draft Game', $names);
    }
}
