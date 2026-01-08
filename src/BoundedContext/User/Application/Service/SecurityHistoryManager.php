<?php

namespace App\BoundedContext\User\Application\Service;

use App\BoundedContext\User\Domain\Entity\SecurityEvent;
use App\SharedKernel\Domain\Security\SecurityEventTypeEnum;
use App\BoundedContext\User\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Manages security event history for auditing and tracking security-related actions
 */
class SecurityHistoryManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * Record a security event for a user
     *
     * @param array<string, mixed> $data
     */
    public function recordEvent(User $user, SecurityEventTypeEnum $eventType, array $data = []): SecurityEvent
    {
        // Record password change in security history
        $request = $this->requestStack->getCurrentRequest();
        $ip = $request ? $request->getClientIp() : 'unknown';
        $userAgent = $request ? $request->headers->get('User-Agent') : 'unknown';

        // Create a new security event
        $event = new SecurityEvent();
        $event->setUser($user);
        $event->setEventTypeFromEnum($eventType);
        $event->setEventData($data);
        $event->setCreatedAt(new \DateTime());
        $event->setIpAddress($ip);
        $event->setUserAgent($userAgent);

        // Save the event
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return $event;
    }

    /**
     * Get recent security events for a user
     *
     * @return SecurityEvent[]
     */
    public function getRecentEvents(User $user, int $limit = 10): array
    {
        return $this->entityManager->getRepository(SecurityEvent::class)
            ->createQueryBuilder('e')
            ->where('e.user = :user')
            ->setParameter('user', $user)
            ->orderBy('e.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get events of a specific type for a user
     *
     * @return SecurityEvent[]
     */
    public function getEventsByType(User $user, string $eventType, int $limit = 10): array
    {
        return $this->entityManager->getRepository(SecurityEvent::class)
            ->createQueryBuilder('e')
            ->where('e.user = :user')
            ->andWhere('e.eventType = :eventType')
            ->setParameter('user', $user)
            ->setParameter('eventType', $eventType)
            ->orderBy('e.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Check if a specific event has occurred for a user within a time period
     */
    public function hasEventOccurredWithin(User $user, string $eventType, \DateInterval $interval): bool
    {
        $date = new \DateTime();
        $date->sub($interval);

        $count = $this->entityManager->getRepository(SecurityEvent::class)
            ->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.user = :user')
            ->andWhere('e.eventType = :eventType')
            ->andWhere('e.createdAt >= :date')
            ->setParameter('user', $user)
            ->setParameter('eventType', $eventType)
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
