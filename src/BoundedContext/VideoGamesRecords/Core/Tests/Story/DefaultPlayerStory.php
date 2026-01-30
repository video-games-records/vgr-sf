<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Tests\Story;

use App\BoundedContext\VideoGamesRecords\Core\Tests\Factory\PlayerFactory;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerStatusEnum;
use Zenstruck\Foundry\Story;

final class DefaultPlayerStory extends Story
{
    public function build(): void
    {
        // Créer quelques joueurs de référence
        PlayerFactory::new()
            ->topPlayer()
            ->withPseudo('TopPlayer')
            ->withUserId(1)
            ->create([
                'id' => 1,
                'status' => PlayerStatusEnum::MEMBER,
            ]);

        PlayerFactory::new()
            ->active()
            ->withPseudo('ActiveGamer')
            ->withUserId(2)
            ->create([
                'id' => 2,
                'status' => PlayerStatusEnum::MEMBER,
            ]);

        PlayerFactory::new()
            ->newPlayer()
            ->withPseudo('NewbieMaster')
            ->withUserId(3)
            ->create([
                'id' => 3,
                'status' => PlayerStatusEnum::MEMBER,
            ]);

        PlayerFactory::new()
            ->donor()
            ->withPseudo('GenerousDonor')
            ->withUserId(4)
            ->create([
                'id' => 4,
                'status' => PlayerStatusEnum::MEMBER,
            ]);

        PlayerFactory::new()
            ->withPseudo('AdminUser')
            ->withUserId(5)
            ->create([
                'id' => 5,
                'status' => PlayerStatusEnum::ADMINISTRATOR,
            ]);

        PlayerFactory::new()
            ->withPseudo('ModeratorUser')
            ->withUserId(6)
            ->create([
                'id' => 6,
                'status' => PlayerStatusEnum::MODERATOR,
            ]);

        // Créer quelques joueurs supplémentaires aléatoires
        PlayerFactory::new()
            ->many(5)
            ->create([
                'status' => PlayerStatusEnum::MEMBER,
            ]);
    }

    public static function topPlayer(): object
    {
        return PlayerFactory::find(['pseudo' => 'TopPlayer']);
    }

    public static function activeGamer(): object
    {
        return PlayerFactory::find(['pseudo' => 'ActiveGamer']);
    }

    public static function newbie(): object
    {
        return PlayerFactory::find(['pseudo' => 'NewbieMaster']);
    }

    public static function donor(): object
    {
        return PlayerFactory::find(['pseudo' => 'GenerousDonor']);
    }

    public static function adminUser(): object
    {
        return PlayerFactory::find(['pseudo' => 'AdminUser']);
    }

    public static function moderatorUser(): object
    {
        return PlayerFactory::find(['pseudo' => 'ModeratorUser']);
    }
}
