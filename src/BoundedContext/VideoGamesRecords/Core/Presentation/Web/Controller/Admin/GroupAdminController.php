<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartLib;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;
use App\BoundedContext\VideoGamesRecords\Core\Presentation\Form\CopyGroupForm;
use App\BoundedContext\VideoGamesRecords\Core\Presentation\Form\Type\ChartTypeType;
use App\BoundedContext\VideoGamesRecords\Core\Presentation\Form\VideoProofOnly;

class GroupAdminController extends CRUDController
{
    /**
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function copyAction(int $id, Request $request): Response
    {
        /** @var Group $group */
        $group = $this->admin->getSubject();

        $em = $this->admin->getModelManager()->getEntityManager($this->admin->getClass());
        $form = $this->createForm(CopyGroupForm::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $em->getRepository(Group::class)->copy($group, $data['withLibs']);

            $this->addFlash('sonata_flash_success', 'Group was successfully copied.');
            return new RedirectResponse($this->admin->generateUrl('show', ['id' => $group->getId()]));
        }

        return $this->render(
            '@VideoGamesRecordsCore/admin/form/form.default.html.twig',
            [
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'admin' => $this->admin,
                'object' => $group,
                'form' => $form,
                'title' => 'Copy => ' . $group->getGame()->getName() . ' / ' . $group->getName(),
                'action' => 'edit'
            ]
        );
    }

    /**
     * @param         $id
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function addLibChartAction(int $id, Request $request): RedirectResponse|Response
    {
        /** @var Group $group */
        $group = $this->admin->getSubject();

        if ($group->getGame()->getGameStatus()->isActive()) {
            $this->addFlash('sonata_flash_error', 'Game is already activated');
            return new RedirectResponse(
                $this->admin->generateUrl(
                    'list',
                    ['filter' => $this->admin->getFilterParameters()]
                )
            );
        }

        $em = $this->admin->getModelManager()->getEntityManager($this->admin->getClass());
        $form = $this->createForm(ChartTypeType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $type = $data['type'];
            $chartType = $em->getRepository(\App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartType::class)->find($type);

            foreach ($group->getCharts() as $chart) {
                $libChart = new ChartLib();
                $libChart->setType($chartType);
                $chart->addLib($libChart);
                $em->persist($libChart);
            }
            $em->flush();

            $this->addFlash('sonata_flash_success', 'Add all libchart on group successfully');
            return new RedirectResponse($this->admin->generateUrl('show', ['id' => $group->getId()]));
        }

        return $this->render(
            '@VideoGamesRecordsCore/admin/object/group/form.add_libchart.html.twig',
            [
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'admin' => $this->admin,
                'object' => $group,
                'form' => $form,
                'group' => $group,
                'action' => 'edit'
            ]
        );
    }

    /**
     * @param         $id
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function setVideoProofOnlyAction(int $id, Request $request): RedirectResponse|Response
    {
        /** @var Group $group */
        $group = $this->admin->getSubject();

        $em = $this->admin->getModelManager()->getEntityManager($this->admin->getClass());
        $form = $this->createForm(VideoProofOnly::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $isVideoProofOnly = $data['isVideoProofOnly'];
            foreach ($group->getCharts() as $chart) {
                $chart->setIsProofVideoOnly($isVideoProofOnly);
            }
            $em->flush();

            $this->addFlash('sonata_flash_success', 'All charts are updated successfully');
            return new RedirectResponse($this->admin->generateUrl('show', ['id' => $group->getId()]));
        }

        return $this->render(
            '@VideoGamesRecordsCore/admin/form/form.set_video_proof_only.html.twig',
            [
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'admin' => $this->admin,
                'object' => $group,
                'form' => $form,
                'title' => $group->getGame()->getName() . ' / ' . $group->getName(),
                'action' => 'edit'
            ]
        );
    }
}
