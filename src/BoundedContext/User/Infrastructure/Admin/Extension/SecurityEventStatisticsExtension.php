<?php

namespace App\BoundedContext\User\Infrastructure\Admin\Extension;

use Doctrine\ORM\EntityManagerInterface;
use App\BoundedContext\User\Domain\Entity\SecurityEvent;
use App\SharedKernel\Domain\Security\SecurityEventTypeEnum;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * @extends AbstractAdminExtension<SecurityEvent>
 */
class SecurityEventStatisticsExtension extends AbstractAdminExtension
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function configureRoutes(AdminInterface $admin, RouteCollectionInterface $collection): void
    {
        $collection->add('statistics', 'statistics', [
            '_controller' => 'App\BoundedContext\User\Presentation\Web\Controller\Admin\SecurityEventStatisticsController::statisticsAction'
        ]);
    }

    /**
     * @param array<string, mixed> $list
     * @return array<string, mixed>
     */
    public function configureActionButtons(AdminInterface $admin, array $list, string $action, ?object $object = null): array
    {
        $list['statistics'] = [
            'template' => '@PnUser/admin/statistics_button.html.twig'
        ];

        return $list;
    }

    /**
     * @return array<int, array{type: SecurityEventTypeEnum, count: int, label: string, icon: string, severity: string}>
     */
    public function getSecurityStatistics(int $weeks = 4): array
    {
        $startDate = new \DateTime(sprintf('-%d weeks', $weeks));

        $eventTypes = [
            SecurityEventTypeEnum::PASSWORD_CHANGE->value,
            SecurityEventTypeEnum::PASSWORD_RESET_REQUEST->value,
            SecurityEventTypeEnum::PASSWORD_RESET_COMPLETE->value,
            SecurityEventTypeEnum::EMAIL_CHANGE->value,
            SecurityEventTypeEnum::REGISTRATION->value,
        ];

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('se.eventType, COUNT(se.id) as count')
           ->from(SecurityEvent::class, 'se')
           ->where('se.createdAt >= :startDate')
           ->andWhere('se.eventType IN (:eventTypes)')
           ->setParameter('startDate', $startDate)
           ->setParameter('eventTypes', $eventTypes)
           ->groupBy('se.eventType')
           ->orderBy('count', 'DESC');

        $results = $qb->getQuery()->getResult();

        $statistics = [];
        foreach ($results as $result) {
            $eventType = SecurityEventTypeEnum::from($result['eventType']);
            $statistics[] = [
                'type' => $eventType,
                'count' => $result['count'],
                'label' => $eventType->getLabel(),
                'icon' => $eventType->getIcon(),
                'severity' => $eventType->getSeverity()
            ];
        }

        // Ajouter les types sans événements
        foreach ($eventTypes as $type) {
            $found = false;
            foreach ($statistics as $stat) {
                if ($stat['type']->value === $type) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $eventType = SecurityEventTypeEnum::from($type);
                $statistics[] = [
                    'type' => $eventType,
                    'count' => 0,
                    'label' => $eventType->getLabel(),
                    'icon' => $eventType->getIcon(),
                    'severity' => $eventType->getSeverity()
                ];
            }
        }

        return $statistics;
    }

    /**
     * @return array<int, array{week: string, year: string, start_date: \DateTime, end_date: \DateTime, data: array<string, int>}>
     */
    public function getWeeklyTrends(int $weeks = 4): array
    {
        $trends = [];
        $eventTypes = [
            SecurityEventTypeEnum::PASSWORD_CHANGE->value,
            SecurityEventTypeEnum::PASSWORD_RESET_REQUEST->value,
            SecurityEventTypeEnum::PASSWORD_RESET_COMPLETE->value,
            SecurityEventTypeEnum::EMAIL_CHANGE->value,
            SecurityEventTypeEnum::REGISTRATION->value,
        ];

        // Semaine en cours + 4 dernières semaines complètes = 5 semaines au total
        for ($i = $weeks; $i >= 0; $i--) {
            // Calculer le début de la semaine (lundi)
            $weekStart = new \DateTime();
            $weekStart->modify(sprintf('-%d weeks', $i));
            $weekStart->modify('monday this week');
            $weekStart->setTime(0, 0, 0);

            // Calculer la fin de la semaine (dimanche)
            $weekEnd = clone $weekStart;
            $weekEnd->modify('+6 days');
            $weekEnd->setTime(23, 59, 59);

            // Pour la semaine en cours (i = 0), ne pas dépasser aujourd'hui
            if ($i === 0) {
                $now = new \DateTime();
                if ($weekEnd > $now) {
                    $weekEnd = $now;
                }
            }

            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('se.eventType, COUNT(se.id) as count')
               ->from(SecurityEvent::class, 'se')
               ->where('se.createdAt >= :weekStart')
               ->andWhere('se.createdAt < :weekEnd')
               ->andWhere('se.eventType IN (:eventTypes)')
               ->setParameter('weekStart', $weekStart)
               ->setParameter('weekEnd', $weekEnd)
               ->setParameter('eventTypes', $eventTypes)
               ->groupBy('se.eventType');

            $weekResults = $qb->getQuery()->getResult();

            $weekData = [];
            foreach ($eventTypes as $type) {
                $weekData[$type] = 0;
            }

            foreach ($weekResults as $result) {
                $weekData[$result['eventType']] = $result['count'];
            }

            $trends[] = [
                'week' => $weekStart->format('W'),
                'year' => $weekStart->format('Y'),
                'start_date' => $weekStart,
                'end_date' => $weekEnd,
                'data' => $weekData
            ];
        }

        return $trends;
    }
}
