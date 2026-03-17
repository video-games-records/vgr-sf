<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\Forum\Domain\Entity\Message;
use App\BoundedContext\Forum\Domain\Entity\Topic;
use App\BoundedContext\Forum\Domain\ValueObject\ForumStatus;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function save(Message $message): void
    {
        $this->getEntityManager()->persist($message);
        $this->getEntityManager()->flush();
    }

    public function remove(Message $message): void
    {
        $this->getEntityManager()->remove($message);
        $this->getEntityManager()->flush();
    }

    /**
     * @param string[] $userRoles
     * @return Message[]
     */
    public function findLatest(int $limit = 5, array $userRoles = []): array
    {
        $qb = $this->createQueryBuilder('m')
            ->join('m.topic', 't')
            ->join('t.forum', 'f')
            ->join('m.user', 'u')
            ->addSelect('t', 'f', 'u')
            ->where('t.boolArchive = false')
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit);

        if (empty($userRoles)) {
            // Anonyme : forums publics uniquement
            $qb->andWhere('f.status = :status')
                ->setParameter('status', ForumStatus::PUBLIC);
        } else {
            // Authentifié : forums publics + forums privés accessibles via rôle
            $qb->andWhere('f.status = :status OR (f.status = :private AND f.role IN (:roles))')
                ->setParameter('status', ForumStatus::PUBLIC)
                ->setParameter('private', ForumStatus::PRIVATE)
                ->setParameter('roles', $userRoles);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return \Doctrine\ORM\Query<mixed, mixed>
     */
    public function getMessagesByTopicQuery(Topic $topic): \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('m')
            ->where('m.topic = :topic')
            ->setParameter('topic', $topic)
            ->orderBy('m.position', 'ASC')
            ->getQuery();
    }
}
