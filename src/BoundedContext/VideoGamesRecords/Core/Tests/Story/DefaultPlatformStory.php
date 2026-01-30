<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Tests\Story;

use App\BoundedContext\VideoGamesRecords\Core\Tests\Factory\PlatformFactory;
use Zenstruck\Foundry\Story;

final class DefaultPlatformStory extends Story
{
    public function build(): void
    {
        // Quelques plateformes de référence utilisées dans les tests
        PlatformFactory::new()
            ->withName('Nintendo Switch')
            ->active()
            ->create(['id' => 1]);

        PlatformFactory::new()
            ->withName('PC')
            ->active()
            ->create(['id' => 2]);

        PlatformFactory::new()
            ->withName('PlayStation 4')
            ->active()
            ->create(['id' => 3]);

        PlatformFactory::new()
            ->withName('Xbox One')
            ->active()
            ->create(['id' => 4]);

        // Quelques plateformes supplémentaires aléatoires
        PlatformFactory::new()
            ->inactive()
            ->many(2)
            ->create();
    }

    public static function nintendoSwitch(): object
    {
        return PlatformFactory::find(['name' => 'Nintendo Switch']);
    }

    public static function pc(): object
    {
        return PlatformFactory::find(['name' => 'PC']);
    }

    public static function ps4(): object
    {
        return PlatformFactory::find(['name' => 'PlayStation 4']);
    }

    public static function xboxOne(): object
    {
        return PlatformFactory::find(['name' => 'Xbox One']);
    }
}
