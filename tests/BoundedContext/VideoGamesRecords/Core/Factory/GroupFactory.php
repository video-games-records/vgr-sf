<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Factory;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\GroupOrderBy;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Group>
 */
final class GroupFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Group::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    protected function defaults(): array|callable
    {
        return [
            'libGroupEn' => self::faker()->unique()->words(2, true),
            'libGroupFr' => self::faker()->unique()->words(2, true),
            'orderBy' => GroupOrderBy::NAME,
            'game' => GameFactory::new(),
        ];
    }

    /**
     * Override names
     */
    public function withNames(string $en, ?string $fr = null): static
    {
        return $this->with([
            'libGroupEn' => $en,
            'libGroupFr' => $fr ?? $en,
        ]);
    }

    /**
     * Set order by
     */
    public function withOrderBy(string $orderBy): static
    {
        return $this->with(['orderBy' => $orderBy]);
    }

    /**
     * Set the game relation
     */
    public function forGame(Game $game): static
    {
        return $this->with(['game' => $game]);
    }

    /**
     * Create a DLC group
     */
    public function dlc(): static
    {
        return $this->with(['isDlc' => true]);
    }

    /**
     * Create a ranked group
     */
    public function ranked(): static
    {
        return $this->with(['isRank' => true]);
    }
}
