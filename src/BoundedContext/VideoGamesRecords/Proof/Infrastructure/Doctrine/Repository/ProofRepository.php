<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Infrastructure\Doctrine\Repository;

use App\SharedKernel\Infrastructure\Doctrine\Repository\DefaultRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Proof;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\ProofStatus;

class ProofRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Proof::class);
    }

    /**
     * @return mixed
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countInProgress()
    {
        $qb = $this->createQueryBuilder('proof')
            ->select('COUNT(proof.id)')
            ->where('proof.status = :status')
            ->setParameter('status', ProofStatus::IN_PROGRESS);

        return $qb->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve la prochaine preuve à valider dans le même jeu
     */
    public function findNextInProgressByGame(Game $game, int $excludeId): ?Proof
    {
        return $this->createQueryBuilder('p')
            ->join('p.chart', 'c')
            ->join('c.group', 'g')
            ->join('g.game', 'game')
            ->where('p.status = :status')
            ->andWhere('game.id = :gameId')
            ->andWhere('p.id != :excludeId')
            ->setParameter('status', ProofStatus::IN_PROGRESS)
            ->setParameter('gameId', $game->getId())
            ->setParameter('excludeId', $excludeId)
            ->orderBy('p.createdAt', 'ASC') // Plus ancienne en premier
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Compte les preuves en attente par jeu
     */
    public function countInProgressByGame(Game $game): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->join('p.chart', 'c')
            ->join('c.group', 'g')
            ->join('g.game', 'game')
            ->where('p.status = :status')
            ->andWhere('game.id = :gameId')
            ->setParameter('status', ProofStatus::IN_PROGRESS)
            ->setParameter('gameId', $game->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }


    /**
     * Compte les preuves en attente par jeu
     * @return array<string, mixed>
     */
    public function countInProgressByGames(): array
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->from('App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game', 'gam')
            ->select('gam')
            ->addSelect('COUNT(proof) as nb')
            ->innerJoin('gam.groups', 'grp')
            ->innerJoin('grp.charts', 'chr')
            ->innerJoin('chr.proofs', 'proof')
            ->where('proof.status = :status')
            ->setParameter('status', ProofStatus::IN_PROGRESS)
            ->groupBy('gam.id')
            ->orderBy('nb', 'DESC');

        return $query->getQuery()->getResult();
    }
}
