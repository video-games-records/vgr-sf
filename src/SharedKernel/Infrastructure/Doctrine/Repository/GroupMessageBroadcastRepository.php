<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\Doctrine\Repository;

use Doctrine\Persistence\ManagerRegistry;
use App\BoundedContext\User\Domain\Entity\User;
use App\SharedKernel\Domain\Entity\GroupMessageBroadcast;
use App\SharedKernel\Domain\ValueObject\GroupMessageBroadcastTypeEnum;

/**
 * @extends DefaultRepository<GroupMessageBroadcast>
 */
class GroupMessageBroadcastRepository extends DefaultRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupMessageBroadcast::class);
    }

    /**
     * @return GroupMessageBroadcast[]
     */
    public function findByGroup(GroupMessageBroadcastTypeEnum $groupType, int $groupId): array
    {
        return $this->createQueryBuilder('gmb')
            ->where('gmb.groupType = :groupType')
            ->andWhere('gmb.groupId = :groupId')
            ->setParameter('groupType', $groupType->value)
            ->setParameter('groupId', $groupId)
            ->orderBy('gmb.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<array{id: int|null, subject: string, recipientCount: int, createdAt: \DateTime|null, senderUsername: string}>
     */
    public function getHistoryForGroup(GroupMessageBroadcastTypeEnum $groupType, int $groupId): array
    {
        $broadcasts = $this->findByGroup($groupType, $groupId);
        if (empty($broadcasts)) {
            return [];
        }

        $senderIds = array_values(array_unique(array_map(
            static fn (GroupMessageBroadcast $broadcast): int => $broadcast->getSenderUserId(),
            $broadcasts
        )));

        $usernames = [];
        /** @var User $user */
        foreach ($this->getEntityManager()->getRepository(User::class)->findBy(['id' => $senderIds]) as $user) {
            $usernames[$user->getId()] = $user->getUsername();
        }

        return array_map(
            static fn (GroupMessageBroadcast $broadcast): array => [
                'id' => $broadcast->getId(),
                'subject' => $broadcast->getSubject(),
                'recipientCount' => $broadcast->getRecipientCount(),
                'createdAt' => $broadcast->getCreatedAt(),
                'senderUsername' => $usernames[$broadcast->getSenderUserId()] ?? ('#' . $broadcast->getSenderUserId()),
            ],
            $broadcasts
        );
    }
}
