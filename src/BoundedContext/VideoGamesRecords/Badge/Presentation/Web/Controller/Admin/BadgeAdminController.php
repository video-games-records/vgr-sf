<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Presentation\Web\Controller\Admin;

use App\SharedKernel\Presentation\Web\Controller\Admin\AbstractCRUDController;
use App\SharedKernel\Domain\Exception\PictureAlreadyExistsException;
use App\SharedKernel\Infrastructure\FileSystem\Manager\PictureUploadManager;
use App\SharedKernel\Presentation\Form\PictureUploadType;
use App\BoundedContext\VideoGamesRecords\Badge\Application\Manager\BadgeManager;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\Badge;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends AbstractCRUDController<Badge>
 */
class BadgeAdminController extends AbstractCRUDController
{
    public function __construct(
        private readonly BadgeManager $badgeManager,
        private readonly PictureUploadManager $pictureUploadManager
    ) {
    }

    public function uploadPictureAction(int $id, Request $request): RedirectResponse|Response
    {
        /** @var Badge $badge */
        $badge = $this->admin->getSubject();

        $form = $this->createForm(PictureUploadType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('picture')->getData();

            try {
                $filename = $this->pictureUploadManager->upload($file, $badge->getType()->getDirectory());
                $this->badgeManager->updatePicture($badge, $filename);
                $this->addFlash('sonata_flash_success', 'Picture uploaded successfully');
            } catch (PictureAlreadyExistsException $e) {
                $this->addFlash('sonata_flash_error', $e->getMessage());
            }

            return new RedirectResponse($this->admin->generateUrl('show', ['id' => $badge->getId()]));
        }

        return $this->render(
            '@VideoGamesRecordsBadge/admin/form/form.default.html.twig',
            [
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'admin' => $this->admin,
                'object' => $badge,
                'form' => $form,
                'title' => (string) $badge,
                'action' => 'edit'
            ]
        );
    }
}
