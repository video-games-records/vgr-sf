<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Form\DataTransformerInterface;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;

class UserToPlayerTransformer implements DataTransformerInterface
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @throws ORMException
     */
    public function transform($value): Player
    {
        return $this->em->getReference(Player::class, $value->getId());
    }

    /**
     * @return array<string, mixed>
     */
    public function reverseTransform($value): array
    {
        return [];
    }
}
