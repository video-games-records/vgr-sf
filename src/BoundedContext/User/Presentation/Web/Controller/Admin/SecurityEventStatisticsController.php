<?php

namespace App\BoundedContext\User\Presentation\Web\Controller\Admin;

use App\BoundedContext\User\Infrastructure\Admin\Extension\SecurityEventStatisticsExtension;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;

class SecurityEventStatisticsController extends CRUDController
{
    public function __construct(
        private readonly SecurityEventStatisticsExtension $statisticsExtension
    ) {
    }

    public function statisticsAction(): Response
    {
        $statistics = $this->statisticsExtension->getSecurityStatistics(4);
        $trends = $this->statisticsExtension->getWeeklyTrends(4);

        // Préparer les types d'événements pour le template
        $eventTypes = [
            'password_change' => \App\SharedKernel\Domain\Security\SecurityEventTypeEnum::PASSWORD_CHANGE,
            'password_reset_request' => \App\SharedKernel\Domain\Security\SecurityEventTypeEnum::PASSWORD_RESET_REQUEST,
            'password_reset_complete' => \App\SharedKernel\Domain\Security\SecurityEventTypeEnum::PASSWORD_RESET_COMPLETE,
            'email_change' => \App\SharedKernel\Domain\Security\SecurityEventTypeEnum::EMAIL_CHANGE,
            'registration' => \App\SharedKernel\Domain\Security\SecurityEventTypeEnum::REGISTRATION,
        ];

        return $this->renderWithExtraParams('@User/admin/security_statistics.html.twig', [
            'statistics' => $statistics,
            'trends' => $trends,
            'eventTypes' => $eventTypes,
            'admin' => $this->admin,
            'object' => null,
        ]);
    }
}
