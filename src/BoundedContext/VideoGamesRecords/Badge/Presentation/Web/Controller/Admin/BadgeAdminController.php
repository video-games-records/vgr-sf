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
use Symfony\Component\HttpFoundation\JsonResponse;
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

            $result = $this->processPictureUpload($badge, $file);
            if ($result['success']) {
                $this->addFlash('sonata_flash_success', 'Picture uploaded successfully');
            } else {
                $this->addFlash('sonata_flash_error', $result['message']);
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

    public function uploadPictureAjaxAction(int $id, Request $request): JsonResponse
    {
        try {
            $badge = $this->admin->getSubject();
        } catch (\LogicException) {
            return new JsonResponse(['success' => false, 'message' => 'Badge not found.'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->admin->hasAccess('edit', $badge)) {
            return new JsonResponse(['success' => false, 'message' => 'Access denied.'], Response::HTTP_FORBIDDEN);
        }

        if (!$this->isCsrfTokenValid('badge_upload_picture_ajax', (string) $request->request->get('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid CSRF token.'], Response::HTTP_BAD_REQUEST);
        }

        $file = $request->files->get('picture');
        if (!$file instanceof UploadedFile) {
            return new JsonResponse(['success' => false, 'message' => 'No file provided.'], Response::HTTP_BAD_REQUEST);
        }

        $error = $this->pictureUploadManager->validate($file);
        if ($error !== null) {
            return new JsonResponse(['success' => false, 'message' => $error], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->processPictureUpload($badge, $file);

        return new JsonResponse($result, $result['success'] ? Response::HTTP_OK : Response::HTTP_CONFLICT);
    }

    /**
     * @return array{success: bool, filename?: string, message: string|null}
     */
    private function processPictureUpload(Badge $badge, UploadedFile $file): array
    {
        try {
            $filename = $this->pictureUploadManager->upload($file, $badge->getType()->getDirectory());
            $this->badgeManager->updatePicture($badge, $filename);

            return ['success' => true, 'filename' => $filename, 'message' => null];
        } catch (PictureAlreadyExistsException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Could not process the uploaded image.'];
        }
    }
}
