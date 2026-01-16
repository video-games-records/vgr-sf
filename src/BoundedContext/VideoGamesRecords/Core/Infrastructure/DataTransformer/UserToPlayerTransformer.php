<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Form\DataTransformerInterface;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;

/**
 * @implements DataTransformerInterface<array<string, mixed>, Player>
 */
class UserToPlayerTransformer implements DataTransformerInterface
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Transforms a value from the original representation to a transformed representation.
     *
     * @param mixed $value The value in the original representation (Player or User)
     * @return Player|null The value in the transformed representation
     * @throws ORMException
     */
    public function transform(mixed $value): ?Player
    {
        if ($value === null) {
            return null;
        }

        /** @var Player $player */
        $player = $this->em->getReference(Player::class, $value->getId());
        return $player;
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
