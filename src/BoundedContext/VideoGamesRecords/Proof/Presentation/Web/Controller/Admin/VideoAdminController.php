<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Presentation\Web\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\BoundedContext\VideoGamesRecords\Proof\Application\Handler\YoutubeDataHandler;

class VideoAdminController extends CRUDController
{
    //private YoutubeDataHandler $youtubeDataHandler;

    public function __construct(/* YoutubeDataHandler $youtubeDataHandler */)
    {
        //$this->youtubeDataHandler = $youtubeDataHandler;
    }


    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function majAction(int $id): RedirectResponse
    {
        //$this->youtubeDataHandler->process($this->admin->getSubject());
        $this->addFlash('sonata_flash_success', 'Video data maj successfully - Handler to be implemented');
        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
