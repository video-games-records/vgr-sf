<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\VideoGamesRecords\Core\Factory;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerStatusEnum;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use DateTime;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Player>
 */
final class PlayerFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Player::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    protected function defaults(): array|callable
    {
        return [
            'user_id' => self::faker()->unique()->numberBetween(1, 999999),
            'pseudo' => self::faker()->unique()->userName(),
            'avatar' => 'default.jpg',
            'gamerCard' => self::faker()->optional()->word(),
            'rankProof' => 0,
            'rankCountry' => 0,
            'nbChartMax' => 0,
            'nbChartWithPlatform' => 0,
            'nbChartDisabled' => 0,
            'lastLogin' => self::faker()->optional()->dateTimeBetween('-1 year'),
            'nbConnexion' => self::faker()->numberBetween(0, 1000),
            'boolMaj' => false,
            'hasDonate' => false,
            'team' => null,
            'lastDisplayLostPosition' => null,
            'status' => self::faker()->randomElement(PlayerStatusEnum::cases()),
            // Traits defaults
            'rankCup' => 0,
            'gameRank0' => 0,
            'gameRank1' => 0,
            'gameRank2' => 0,
            'gameRank3' => 0,
            'rankMedal' => 0,
            'chartRank0' => 0,
            'chartRank1' => 0,
            'chartRank2' => 0,
            'chartRank3' => 0,
            'chartRank4' => 0,
            'chartRank5' => 0,
            'rankBadge' => 0,
            'pointBadge' => 0,
            'rankPointChart' => 0,
            'pointChart' => 0,
            'rankPointGame' => 0,
            'pointGame' => 0,
            'averageChartRank' => 0.0,
            'averageGameRank' => 0.0,
            'nbChart' => 0,
            'nbChartProven' => 0,
            'nbGame' => 0,
            'nbVideo' => 0,
            'nbMasterBadge' => 0,
            // PlayerPersonalDataTrait defaults
            'presentation' => null,
            'collection' => null,
            'birthDate' => null,
            'gender' => 'I',
            'country' => null,
            'displayPersonalInfos' => false,
        ];
    }

    /**
     * Create an active player with recent activity
     */
    public function active(): static
    {
        return $this->with([
            'lastLogin' => self::faker()->dateTimeBetween('-1 week'),
            'nbConnexion' => self::faker()->numberBetween(10, 500),
            'boolMaj' => true,
        ]);
    }

    /**
     * Create a new player with minimal activity
     */
    public function newPlayer(): static
    {
        return $this->with([
            'lastLogin' => self::faker()->dateTimeBetween('-1 month', '-1 week'),
            'nbConnexion' => self::faker()->numberBetween(1, 10),
            'boolMaj' => false,
        ]);
    }

    /**
     * Create a top player with high stats
     */
    public function topPlayer(): static
    {
        return $this->with([
            'lastLogin' => self::faker()->dateTimeBetween('-3 days'),
            'nbConnexion' => self::faker()->numberBetween(500, 2000),
            'pointGame' => self::faker()->numberBetween(10000, 50000),
            'pointChart' => self::faker()->numberBetween(5000, 25000),
            'nbGame' => self::faker()->numberBetween(50, 200),
            'nbChart' => self::faker()->numberBetween(100, 500),
            'nbMasterBadge' => self::faker()->numberBetween(5, 20),
            'rankPointGame' => self::faker()->numberBetween(1, 100),
            'rankPointChart' => self::faker()->numberBetween(1, 100),
            'boolMaj' => true,
            'hasDonate' => true,
        ]);
    }

    /**
     * Create a player who has donated
     */
    public function donor(): static
    {
        return $this->with([
            'hasDonate' => true,
        ]);
    }

    /**
     * Override pseudo
     */
    public function withPseudo(string $pseudo): static
    {
        return $this->with(['pseudo' => $pseudo]);
    }

    /**
     * Override user ID
     */
    public function withUserId(int $userId): static
    {
        return $this->with(['user_id' => $userId]);
    }

    /**
     * Override avatar
     */
    public function withAvatar(string $avatar): static
    {
        return $this->with(['avatar' => $avatar]);
    }

    /**
     * Override status
     */
    public function withStatus(PlayerStatusEnum $status): static
    {
        return $this->with(['status' => $status]);
    }

    /**
     * Create a player with member status
     */
    public function member(): static
    {
        return $this->with(['status' => PlayerStatusEnum::MEMBER]);
    }

    /**
     * Create a player with admin status
     */
    public function admin(): static
    {
        $adminStatuses = [
            PlayerStatusEnum::WEBMASTER,
            PlayerStatusEnum::ADMINISTRATOR,
            PlayerStatusEnum::PROOF_ADMIN,
            PlayerStatusEnum::GAME_AND_PROOF_ADMIN,
            PlayerStatusEnum::CHIEF_PROOF_ADMIN,
            PlayerStatusEnum::CHIEF_STAFF,
        ];

        return $this->with(['status' => self::faker()->randomElement($adminStatuses)]);
    }

    /**
     * Set team
     */
    public function withTeam(Team $team): static
    {
        return $this->with(['team' => $team]);
    }
}
