<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Admin;

use App\BoundedContext\VideoGamesRecords\Core\Application\Manager\SerieManager;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Serie;
use App\SharedKernel\Domain\Exception\PictureAlreadyExistsException;
use App\SharedKernel\Infrastructure\FileSystem\Manager\PictureUploadManager;
use App\SharedKernel\Presentation\Form\PictureUploadType;
use App\SharedKernel\Presentation\Web\Controller\Admin\AbstractCRUDController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Player\UpdatePlayerSerieRank;

/**
 * @extends AbstractCRUDController<Serie>
 */
class SerieAdminController extends AbstractCRUDController
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly SerieManager $serieManager,
        private readonly PictureUploadManager $pictureUploadManager
    ) {
    }

    /**
     * @param int $id
     * @return RedirectResponse
     * @throws ExceptionInterface
     */
    public function majAction(int $id): RedirectResponse
    {
        $this->bus->dispatch(new UpdatePlayerSerieRank((int) $this->admin->getSubject()->getId()));
        $this->addFlash('sonata_flash_success', 'Serie maj successfully');
        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    public function uploadPictureAction(int $id, Request $request): RedirectResponse|Response
    {
        /** @var Serie $serie */
        $serie = $this->admin->getSubject();

        $form = $this->createForm(PictureUploadType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('picture')->getData();

            try {
                $filename = $this->pictureUploadManager->upload($file, 'series');
                $this->serieManager->updatePicture($serie, $filename);
                $this->addFlash('sonata_flash_success', 'Picture uploaded successfully');
            } catch (PictureAlreadyExistsException $e) {
                $this->addFlash('sonata_flash_error', $e->getMessage());
            }

            return new RedirectResponse($this->admin->generateUrl('show', ['id' => $serie->getId()]));
        }

        return $this->render(
            '@VideoGamesRecordsCore/admin/form/form.default.html.twig',
            [
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'admin' => $this->admin,
                'object' => $serie,
                'form' => $form,
                'title' => $serie->getLibSerie(),
                'action' => 'edit'
            ]
        );
    }
}
