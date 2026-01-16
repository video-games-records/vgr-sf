<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Doctrine\Repository;

use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\Genre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Genre>
 */
class GenreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Genre::class);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function findByIgdbData(array $data): ?Genre
    {
        return $this->find($data['id']);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createFromIgdbData(array $data): Genre
    {
        $genre = new Genre();
        $this->updateFromIgdbData($genre, $data);

        return $genre;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateFromIgdbData(Genre $genre, array $data): void
    {
        $genre->setId($data['id']);
        $genre->setName($data['name']);
        $genre->setSlug($data['slug']);
        $genre->setUrl($data['url'] ?? null);

        if (isset($data['updated_at'])) {
            $genre->setUpdatedAt(new \DateTimeImmutable('@' . $data['updated_at']));
        }

        if (isset($data['created_at'])) {
            $genre->setCreatedAt(new \DateTimeImmutable('@' . $data['created_at']));
        }
    }
}
