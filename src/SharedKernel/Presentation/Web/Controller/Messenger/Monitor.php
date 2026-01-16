<?php

declare(strict_types=1);

namespace App\SharedKernel\Presentation\Web\Controller\Messenger;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Zenstruck\Messenger\Monitor\Controller\MessengerMonitorController as BaseMessengerMonitorController;

#[Route('/admin/messenger')]
#[IsGranted('ROLE_MESSENGER_MONITOR')]
final class Monitor extends BaseMessengerMonitorController
{
}
