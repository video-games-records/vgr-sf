<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerSerieRank;

class SerieAdminController extends CRUDController
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    /**
     * @param $id
     * @return RedirectResponse
     * @throws ExceptionInterface
     */
    public function majAction(int $id): RedirectResponse
    {
        $this->bus->dispatch(new UpdatePlayerSerieRank($this->admin->getSubject()->getId()));
        $this->addFlash('sonata_flash_success', 'Serie maj successfully');
        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
