<?php

namespace App\BoundedContext\Message\Domain\Repository;

use App\BoundedContext\Message\Domain\Entity\Message;
use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\ORM\QueryBuilder;

interface MessageRepositoryInterface
{
    public function purge(): void;

    /**
     * @return array<mixed>
     */
    public function getRecipients(User $user): array;

    /**
     * @return array<mixed>
     */
    public function getSenders(User $user): array;

    public function getNbNewMessage(User $user): int;

    /**
     * @param array<string, mixed> $filters
     */
    public function getInboxMessages(User $user, array $filters = []): QueryBuilder;

    /**
     * @param array<string, mixed> $filters
     */
    public function getOutboxMessages(User $user, array $filters = []): QueryBuilder;
}
