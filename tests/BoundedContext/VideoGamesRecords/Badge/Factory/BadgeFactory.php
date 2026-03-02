<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Badge\Factory;

use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\Badge;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\ValueObject\BadgeType;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Badge>
 */
final class BadgeFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Badge::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    protected function defaults(): array|callable
    {
        return [
            'type' => self::faker()->randomElement(BadgeType::cases()),
            'picture' => 'default.gif',
            'value' => 0,
        ];
    }

    /**
     * Create the register badge (id=1)
     */
    public static function register(): static
    {
        return static::new()->with([
            'type' => BadgeType::INSCRIPTION,
            'picture' => 'inscription.gif',
            'value' => 0,
        ]);
    }

    /**
     * Create badge with specific type
     */
    public function withType(BadgeType $type): static
    {
        return $this->with(['type' => $type]);
    }

    /**
     * Create badge with specific picture
     */
    public function withPicture(string $picture): static
    {
        return $this->with(['picture' => $picture]);
    }

    /**
     * Create badge with specific value
     */
    public function withValue(int $value): static
    {
        return $this->with(['value' => $value]);
    }

    /**
     * Create connection badge
     */
    public function connection(): static
    {
        return $this->with([
            'type' => BadgeType::CONNEXION,
            'picture' => 'connexion.gif',
        ]);
    }

    /**
     * Create forum badge
     */
    public function forum(): static
    {
        return $this->with([
            'type' => BadgeType::FORUM,
            'picture' => 'forum.gif',
        ]);
    }
}
