<?php

declare(strict_types=1);

namespace App\BoundedContext\Message\Infrastructure\Doctrine\Repository;

use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\Message\Domain\Entity\Message;
use App\BoundedContext\Message\Domain\Repository\MessageRepositoryInterface;
use App\BoundedContext\User\Domain\Entity\User;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository implements MessageRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function purge(): void
    {
        // delete 1
        $query = $this->getEntityManager()->createQuery(
            'DELETE App\BoundedContext\Message\Domain\Entity\Message m
            WHERE m.isDeletedSender = :isDeletedSender
            AND m.isDeletedRecipient = :isDeletedRecipient'
        );
        $query->setParameter('isDeletedSender', true);
        $query->setParameter('isDeletedRecipient', true);
        $query->execute();

        // delete 2
        $date = new DateTime();
        $date = $date->sub(DateInterval::createFromDateString('2 years'));
        $query = $this->getEntityManager()->createQuery(
            'DELETE App\BoundedContext\Message\Domain\Entity\Message m WHERE m.createdAt < :date'
        );
        $query->setParameter('date', $date->format('Y-m-d'));
        $query->execute();
    }

    /**
     * @return array<mixed>
     */
    public function getRecipients(User $user): array
    {
        $query = $this->createQueryBuilder('m')
            ->join('m.recipient', 'u')
            ->select('DISTINCT u.id,u.username')
            ->where('m.sender = :user')
            ->setParameter('user', $user)
            ->andWhere('m.isDeletedSender = :isDeletedSender')
            ->setParameter('isDeletedSender', false)
            ->orderBy("u.username", 'ASC');

        return $query->getQuery()->getResult();
    }

    /**
     * @return array<mixed>
     */
    public function getSenders(User $user): array
    {
        $query = $this->createQueryBuilder('m')
            ->join('m.sender', 'u')
            ->select('DISTINCT u.id,u.username')
            ->where('m.recipient = :user')
            ->setParameter('user', $user)
            ->andWhere('m.isDeletedRecipient = :isDeletedRecipient')
            ->setParameter('isDeletedRecipient', false)
            ->orderBy("u.username", 'ASC');

        return $query->getQuery()->getResult();
    }

    public function getNbNewMessage(User $user): int
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.recipient = :recipient')
            ->andWhere('m.isOpened = :isOpened')
            ->andWhere('m.isDeletedRecipient = :isDeletedRecipient')
            ->setParameter('recipient', $user)
            ->setParameter('isOpened', false)
            ->setParameter('isDeletedRecipient', false);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function getInboxMessages(User $user, array $filters = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.sender', 's')
            ->leftJoin('m.recipient', 'r')
            ->where('m.recipient = :user')
            ->andWhere('m.isDeletedRecipient = :isDeletedRecipient')
            ->setParameter('user', $user)
            ->setParameter('isDeletedRecipient', false)
            ->orderBy('m.id', 'DESC');

        // Recherche globale
        if (isset($filters['search']) && !empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('m.object', ':search'),
                    $qb->expr()->like('m.message', ':search'),
                    $qb->expr()->like('s.username', ':search'),
                    $qb->expr()->like('r.username', ':search')
                )
            )->setParameter('search', $searchTerm);
        }

        // Appliquer les autres filtres
        if (isset($filters['type'])) {
            $qb->andWhere('m.type = :type')
                ->setParameter('type', $filters['type']);
        }

        if (isset($filters['sender'])) {
            $qb->andWhere('m.sender = :sender')
                ->setParameter('sender', $filters['sender']);
        }

        if (isset($filters['isOpened'])) {
            $qb->andWhere('m.isOpened = :isOpened')
                ->setParameter('isOpened', $filters['isOpened']);
        }

        return $qb;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function getOutboxMessages(User $user, array $filters = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.sender', 's')
            ->leftJoin('m.recipient', 'r')
            ->where('m.sender = :user')
            ->andWhere('m.isDeletedSender = :isDeletedSender')
            ->setParameter('user', $user)
            ->setParameter('isDeletedSender', false)
            ->orderBy('m.id', 'DESC');

        // Recherche globale
        if (isset($filters['search']) && !empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('m.object', ':search'),
                    $qb->expr()->like('m.message', ':search'),
                    $qb->expr()->like('s.username', ':search'),
                    $qb->expr()->like('r.username', ':search')
                )
            )->setParameter('search', $searchTerm);
        }

        // Appliquer les autres filtres
        if (isset($filters['type'])) {
            $qb->andWhere('m.type = :type')
                ->setParameter('type', $filters['type']);
        }

        if (isset($filters['recipient'])) {
            $qb->andWhere('m.recipient = :recipient')
                ->setParameter('recipient', $filters['recipient']);
        }

        if (isset($filters['isOpened'])) {
            $qb->andWhere('m.isOpened = :isOpened')
                ->setParameter('isOpened', $filters['isOpened']);
        }

        return $qb;
    }
}
