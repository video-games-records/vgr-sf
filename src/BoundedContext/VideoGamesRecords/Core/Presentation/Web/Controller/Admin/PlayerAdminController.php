<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Dispatcher\RankingUpdateDispatcher;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerData;

class PlayerAdminController extends CRUDController
{
    public function __construct(private readonly RankingUpdateDispatcher $rankingUpdateDispatcher)
    {
    }

    /**
     * @param $id
     * @return RedirectResponse
     * @throws ExceptionInterface
     */
    public function majAction(int $id): RedirectResponse
    {
        /** @var Player $player */
        $player = $this->admin->getSubject();
        $this->rankingUpdateDispatcher->updatePlayerRankFromPlayer($player);
        $this->addFlash('sonata_flash_success', 'Player maj successfully');
        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
