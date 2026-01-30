<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\DataTransformer;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use Symfony\Component\Form\DataTransformerInterface;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;

/**
 * @implements DataTransformerInterface<array<string, mixed>, Player>
 */
class UserToPlayerTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly PlayerRepository $playerRepository
    ) {
    }

    /**
     * Transforms a value from the original representation to a transformed representation.
     *
     * @param mixed $value The value in the original representation (Player or User)
     * @return Player|null The value in the transformed representation
     */
    public function transform(mixed $value): ?Player
    {
        if ($value === null || !$value instanceof User) {
            return null;
        }

        return $this->playerRepository->getPlayerFromUser($value);
    }

    /**
     * Transforms a value from the transformed representation to its original representation.
     *
     * @param mixed $value The value in the transformed representation (Player)
     * @return array<string, mixed>|null The value in the original representation
     */
    public function reverseTransform(mixed $value): ?array
    {
        return [];
    }
}
