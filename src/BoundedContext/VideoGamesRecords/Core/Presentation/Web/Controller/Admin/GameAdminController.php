<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Web\Controller\Admin;

use App\SharedKernel\Presentation\Web\Controller\Admin\AbstractCRUDController;
use Doctrine\ORM\Exception\ORMException;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Chart;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartLib;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartType;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;
use App\SharedKernel\Presentation\Form\DefaultForm;
use App\BoundedContext\VideoGamesRecords\Core\Presentation\Form\ImportCsv;
use App\BoundedContext\VideoGamesRecords\Core\Presentation\Form\VideoProofOnly;
use App\BoundedContext\VideoGamesRecords\Core\Application\Manager\GameManager;
use App\BoundedContext\VideoGamesRecords\Core\Application\Message\Dispatcher\RankingUpdateDispatcher;

/**
 * @extends AbstractCRUDController<Game>
 */
class GameAdminController extends AbstractCRUDController
{
    public function __construct(
        private readonly GameManager $gameManager,
        private readonly RankingUpdateDispatcher $rankingUpdateDispatcher
    ) {
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response
     */
    public function copyAction(int $id, Request $request): Response
    {
        /** @var Game $game */
        $game = $this->admin->getSubject();

        if (!$this->isGranted(self::ROLE_SUPER_ADMIN)) {
            $this->addFlash('sonata_flash_error', 'The game was not copied.');
            return new RedirectResponse($this->admin->generateUrl('show', ['id' => $game->getId()]));
        }

        $form = $this->createForm(DefaultForm::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->gameManager->copy($game);
            $this->addFlash('sonata_flash_success', 'The game was successfully copied.');
            return new RedirectResponse($this->admin->generateUrl('show', ['id' => $game->getId()]));
        }

        return $this->render(
            '@VideoGamesRecordsCore/admin/form/form.default.html.twig',
            [
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'admin' => $this->admin,
                'object' => $game,
                'form' => $form,
                'title' => 'Copy => ' . $game->getName(),
                'action' => 'edit'
            ]
        );
    }

    /**
     * @param int $id
     * @return RedirectResponse
     * @throws ExceptionInterface
     */
    public function majAction(int $id): RedirectResponse
    {
        /** @var Game $game */
        $game = $this->admin->getSubject();

        $this->rankingUpdateDispatcher->updatePlayerRankFromGame($game);
        $this->addFlash('sonata_flash_success', 'Game maj successfully');
        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * @param int $id
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function setVideoProofOnlyAction(int $id, Request $request): RedirectResponse|Response
    {
        /** @var Game $game */
        $game = $this->admin->getSubject();

        $form = $this->createForm(VideoProofOnly::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $isVideoProofOnly = $data['isVideoProofOnly'];
            $this->gameManager->updateVideoProofOnly($game, $isVideoProofOnly);
            $this->addFlash('sonata_flash_success', 'All charts are updated successfully');
            return new RedirectResponse($this->admin->generateUrl('show', ['id' => $game->getId()]));
        }

        return $this->render(
            '@VideoGamesRecordsCore/admin/form/form.set_video_proof_only.html.twig',
            [
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'admin' => $this->admin,
                'object' => $game,
                'form' => $form,
                'title' => $game->getName(),
                'action' => 'edit'
            ]
        );
    }


    /**
     * @throws ORMException
     */
    public function importCsvAction(int $id, Request $request): Response
    {
        /** @var Game $game */
        $game = $this->admin->getSubject();
        $em = $this->getEntityManager();

        $form = $this->createForm(ImportCsv::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $csvFile */
            $csvFile = $form->get('csv')->getData();

            $rows = str_getcsv($csvFile->getContent(), "\n");

            /** @var Group|null $group */
            $group = null;
            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue;
                }
                $data = str_getcsv((string) $row, ";");
                if (count($data) !== 5) {
                    throw new \RuntimeException('Invalid number of rows.');
                }

                if (!is_numeric($data[4])) {
                    throw new \RuntimeException('Column 5 must be numeric.');
                }
                if ($group === null || $group->getLibGroupEn() !== $data[0]) {
                    $group = new Group();
                    $group->setGame($game);
                    $group->setLibGroupEn((string) $data[0]);
                    $group->setLibGroupFr($data[1]);
                    $em->persist($group);
                }

                $chart = new Chart();
                $chart->setGroup($group);
                $chart->setLibChartEn((string) $data[2]);
                $chart->setLibChartFr($data[3]);
                $em->persist($chart);

                $chartLib = new ChartLib();
                $chartLib->setChart($chart);

                /** @var ChartType $chartType */
                $chartType = $em->getReference(ChartType::class, $data[4]);
                $chartLib->setType($chartType);
                $em->persist($chartLib);
            }
            $em->flush();

            $this->addFlash('sonata_flash_success', 'CSV DATA successfully imported');
            return new RedirectResponse($this->admin->generateUrl('show', ['id' => $game->getId()]));
        }

        return $this->render(
            '@VideoGamesRecordsCore/admin/form/form.default.html.twig',
            [
                'base_template' => '@SonataAdmin/standard_layout.html.twig',
                'admin' => $this->admin,
                'object' => $game,
                'form' => $form,
                'title' => $game->getName(),
                'action' => 'edit'
            ]
        );
    }
}
