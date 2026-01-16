<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Doctrine\Repository;

use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function findByIgdbData(array $data): ?Game
    {
        return $this->find($data['id']);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createFromIgdbData(array $data): Game
    {
        $game = new Game();
        $this->updateFromIgdbData($game, $data);

        return $game;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateFromIgdbData(Game $game, array $data): void
    {
        $game->setId($data['id']);
        $game->setName($data['name']);
        $game->setSlug($data['slug'] ?? null);
        $game->setStoryline($data['storyline'] ?? null);
        $game->setSummary($data['summary'] ?? null);
        $game->setUrl($data['url'] ?? null);
        $game->setChecksum($data['checksum'] ?? null);
        $game->setFirstReleaseDate($data['first_release_date'] ?? null);

        if (isset($data['updated_at'])) {
            $game->setUpdatedAt(new \DateTimeImmutable('@' . $data['updated_at']));
        }

        if (isset($data['created_at'])) {
            $game->setCreatedAt(new \DateTimeImmutable('@' . $data['created_at']));
        }
    }
}
