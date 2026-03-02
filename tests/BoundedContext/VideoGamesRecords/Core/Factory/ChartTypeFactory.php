<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Factory;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartType;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ChartType>
 */
final class ChartTypeFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return ChartType::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->unique()->words(2, true),
            'mask' => '30~',
            'orderBy' => 'ASC',
        ];
    }

    public function withName(string $name): static
    {
        return $this->with(['name' => $name]);
    }

    public function withMask(string $mask): static
    {
        return $this->with(['mask' => $mask]);
    }

    public function orderAsc(): static
    {
        return $this->with(['orderBy' => 'ASC']);
    }

    public function orderDesc(): static
    {
        return $this->with(['orderBy' => 'DESC']);
    }
}
