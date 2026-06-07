<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Presentation\Web\Controller\Admin;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use App\SharedKernel\Presentation\Web\Controller\Admin\AbstractCRUDController;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemOperator;
use Sonata\AdminBundle\Exception\ModelManagerThrowable;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Proof;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\ProofStatus;

/**
 * @extends AbstractCRUDController<Proof>
 */
class ProofAdminController extends AbstractCRUDController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly FilesystemOperator $proofStorage,
    ) {
    }

    public function statsAction(): Response
    {
        $stats = $this->em->getRepository(Player::class)->getProofStats();

        $months = [];
        foreach ($stats as $row) {
            $months[$row['month']][] = $row;
        }

        return $this->render(
            '@VideoGamesRecordsProof/admin/object/proof/stats.html.twig',
            ['stats' => $months]
        );
    }

    public function rotatePictureAction(Request $request): RedirectResponse
    {
        /** @var Proof $proof */
        $proof = $this->assertObjectExists($request, true);
        $this->admin->checkAccess('edit', $proof);

        $picture = $proof->getPicture();

        if ($picture === null) {
            $this->addFlash(
                'sonata_flash_error',
                $this->trans('proof.picture.rotate.no_picture', [], 'VgrProofAdmin')
            );
            return new RedirectResponse($this->admin->generateUrl('edit', ['id' => $proof->getId()]));
        }

        $path = $picture->getPath();
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $imageData = $this->proofStorage->read($path);

        $image = ImageManager::gd()->read($imageData);
        $image->rotate(-90);

        $encoded = match ($extension) {
            'png' => $image->toPng(),
            default => $image->toJpeg(85),
        };

        $imageString = (string) $encoded;
        $this->proofStorage->write($path, $imageString);
        $picture->setHash(hash('sha256', $imageString));
        $this->em->flush();

        $this->addFlash(
            'sonata_flash_success',
            $this->trans('proof.picture.rotate.success', [], 'VgrProofAdmin')
        );

        return new RedirectResponse($this->admin->generateUrl('edit', ['id' => $proof->getId()]));
    }

    public function editAction(Request $request): Response
    {
        /** @var Proof $object */
        $object = $this->assertObjectExists($request, true);
        $this->checkParentChildAssociation($request, $object);

        $this->admin->checkAccess('edit', $object);

        $preResponse = $this->preEdit($request, $object);
        if (null !== $preResponse) {
            return $preResponse;
        }

        $this->admin->setSubject($object);
        $form = $this->admin->getForm();
        $form->setData($object);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $isFormValid = $form->isValid();

            if ($isFormValid) {
                $submittedObject = $form->getData();
                $this->admin->setSubject($submittedObject);

                try {
                    $submittedObject = $this->admin->update($submittedObject);

                    if ($this->isXmlHttpRequest($request)) {
                        return $this->handleXmlHttpRequestSuccessResponse($request, $submittedObject);
                    }

                    $this->addFlash('sonata_flash_success', $this->getSuccessMessage($submittedObject));

                    return $this->getNextProofRedirect($submittedObject);
                } catch (ModelManagerThrowable $e) {
                    $this->handleModelManagerThrowable($e);
                    $isFormValid = false;
                } catch (\Throwable $e) {
                    $this->addFlash('sonata_flash_error', $e->getMessage());
                    $isFormValid = false;
                }
            }

            if (!$isFormValid) { // @phpstan-ignore booleanNot.alwaysTrue
                $this->addFlash(
                    'sonata_flash_error',
                    $this->trans(
                        'flash_edit_error',
                        ['%name%' => $this->escapeHtml($this->admin->toString($object))],
                        'SonataAdminBundle'
                    )
                );
            }
        }

        $formView = $form->createView();
        $this->setFormTheme($formView, $this->admin->getFormTheme());

        return $this->render(
            $this->admin->getTemplateRegistry()->getTemplate('edit'),
            [
                'action' => 'edit',
                'form' => $formView,
                'object' => $object,
                'objectId' => $this->admin->getNormalizedIdentifier($object),
            ]
        );
    }

    private function getSuccessMessage(Proof $proof): string
    {
        $playerName = $proof->getPlayerChart()?->getPlayer()->getPseudo() ?? 'Unknown';

        return match ($proof->getStatus()) {
            ProofStatus::ACCEPTED => $this->trans('proof.success.accepted', ['%player%' => $playerName], 'VgrCoreAdmin'),
            ProofStatus::REFUSED  => $this->trans('proof.success.refused', ['%player%' => $playerName], 'VgrCoreAdmin'),
            default               => $this->trans('flash_edit_success', ['%name%' => $this->escapeHtml($this->admin->toString($proof))], 'SonataAdminBundle'),
        };
    }

    private function getNextProofRedirect(Proof $currentProof): RedirectResponse
    {
        $currentGame = $currentProof->getChart()->getGroup()->getGame();

        $proofRepository = $this->em->getRepository(Proof::class);

        $proofId = $currentProof->getId();
        if ($proofId === null) {
            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $nextProof = $proofRepository->findNextInProgressByGame($currentGame, $proofId);

        if ($nextProof) {
            $remainingCount = $proofRepository->countInProgressByGame($currentGame);
            $this->addFlash(
                'sonata_flash_info',
                $this->trans(
                    'proof.next.redirect.info',
                    [
                        '%game%' => $currentGame->getName(),
                        '%count%' => $remainingCount - 1,
                        '%next_id%' => $nextProof->getId()
                    ],
                    'VgrCoreAdmin'
                )
            );

            return new RedirectResponse(
                $this->admin->generateUrl('edit', ['id' => $nextProof->getId()])
            );
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
