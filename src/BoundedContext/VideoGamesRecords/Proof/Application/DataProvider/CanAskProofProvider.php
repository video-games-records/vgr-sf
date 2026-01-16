<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Application\DataProvider;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;

class CanAskProofProvider
{
    private const MAX_PROOF_REQUEST_DAY = 5;

    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Player $player
     * @return bool
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function load(Player $player): bool
    {
        $qb = $this->em->createQueryBuilder()
            ->from('App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\ProofRequest', 'request')
            ->select('COUNT(request)')
            ->where('request.playerRequesting = :player')
            ->setParameter('player', $player)
            ->andWhere('request.createdAt LIKE :now')
            ->setParameter('now', date('Y-m-d') . '%');

        $nb = $qb->getQuery()->getSingleScalarResult();

        if ($nb >= self::MAX_PROOF_REQUEST_DAY) {
            return false;
        }
        return true;
    }
}
