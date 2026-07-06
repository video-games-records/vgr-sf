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
            ->setMaxResults($limit * 10);

        if (empty($userRoles)) {
            // Anonyme : forums publics uniquement
            $qb->andWhere('f.status = :status')
                ->setParameter('status', ForumStatus::PUBLIC->value);
        } else {
            // Authentifié : forums publics + forums privés accessibles via rôle
            $qb->andWhere('f.status = :status OR (f.status = :private AND f.role IN (:roles))')
                ->setParameter('status', ForumStatus::PUBLIC->value)
                ->setParameter('private', ForumStatus::PRIVATE->value)
                ->setParameter('roles', $userRoles);
        }

        $messages = $qb->getQuery()->getResult();

        // Dédoublonner par topic : garder uniquement le message le plus récent de chaque topic
        $seen = [];
        $unique = [];
        foreach ($messages as $message) {
            $topicId = $message->getTopic()->getId();
            if (!isset($seen[$topicId])) {
                $seen[$topicId] = true;
                $unique[] = $message;
                if (count($unique) === $limit) {
                    break;
                }
            }
        }

        return $unique;
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

    /**
     * Messages postés par un utilisateur, restreints aux forums publics.
     */
    public function getMessagesByUserQueryBuilder(int $userId, ?string $search = null): \Doctrine\ORM\QueryBuilder
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('t', 'f', 'u')
            ->join('m.topic', 't')
            ->join('t.forum', 'f')
            ->join('m.user', 'u')
            ->where('u.id = :userId')
            ->andWhere('f.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', ForumStatus::PUBLIC->value)
            ->orderBy('m.createdAt', 'DESC');

        if ($search !== null && $search !== '') {
            $qb->andWhere('t.name LIKE :search OR m.message LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return $qb;
    }
}
