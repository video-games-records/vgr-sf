<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Factory;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Country;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\CountryTranslation;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Country>
 */
final class CountryFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Country::class;
    }

    protected function defaults(): array|callable
    {
        return function () {
            $name = self::faker()->country();
            return [
                'codeIso2' => self::faker()->countryCode(),
                'codeIso3' => self::faker()->unique()->regexify('[A-Z]{3}'),
                'codeIsoNumeric' => self::faker()->unique()->numberBetween(1, 999),
                'slug' => self::faker()->unique()->slug(2),
            ];
        };
    }

    protected function initialize(): static
    {
        return $this->afterInstantiate(function (Country $country): void {
            // Always create a default English translation
            $translation = new CountryTranslation();
            $translation->setTranslatable($country);
            $translation->setLocale('en');
            $translation->setName(self::faker()->country());
            $country->addTranslation($translation);
        });
    }

    public function withTranslation(string $name, string $locale = 'en'): static
    {
        return $this->afterInstantiate(function (Country $country) use ($name, $locale): void {
            $translation = new CountryTranslation();
            $translation->setTranslatable($country);
            $translation->setLocale($locale);
            $translation->setName($name);
            $country->addTranslation($translation);
        });
    }

    public function france(): static
    {
        return $this->with([
            'codeIso2' => 'FR',
            'codeIso3' => 'FRA',
            'codeIsoNumeric' => 250,
            'slug' => 'france',
        ])->withTranslation('France', 'en')->withTranslation('France', 'fr');
    }

    public function usa(): static
    {
        return $this->with([
            'codeIso2' => 'US',
            'codeIso3' => 'USA',
            'codeIsoNumeric' => 840,
            'slug' => 'usa',
        ])->withTranslation('United States', 'en')->withTranslation('États-Unis', 'fr');
    }
}
