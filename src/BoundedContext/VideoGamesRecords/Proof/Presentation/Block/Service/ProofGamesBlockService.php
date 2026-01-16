<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Proof\Presentation\Block\Service;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use App\BoundedContext\VideoGamesRecords\Proof\Domain\ValueObject\ProofStatus;

class ProofGamesBlockService extends AbstractBlockService
{
    public function __construct(Environment $templating, private readonly EntityManagerInterface $em)
    {
        parent::__construct($templating);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Proof Games Block';
    }


    /**
     * @param BlockContextInterface $blockContext
     * @param Response|null $response
     * @return Response
     */
    public function execute(
        BlockContextInterface $blockContext,
        ?Response $response = null
    ): Response {
        $settings = $blockContext->getSettings();

        $query = $this->em->createQueryBuilder()
            ->from(\App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game::class, 'gam')
            ->select('gam')
            ->addSelect('COUNT(proof) as nb')
            ->innerJoin('gam.groups', 'grp')
            ->innerJoin('grp.charts', 'chr')
            ->innerJoin('chr.proofs', 'proof')
            ->where('proof.status = :status')
            ->setParameter('status', ProofStatus::IN_PROGRESS)
            ->groupBy('gam.id')
            ->orderBy('nb', 'DESC');

        $games = $query->getQuery()->getResult();

        $totalProofsQuery = $this->em->createQueryBuilder()
            ->from(\App\BoundedContext\VideoGamesRecords\Proof\Domain\Entity\Proof::class, 'proof')
            ->select('COUNT(proof.id)')
            ->where('proof.status = :status')
            ->setParameter('status', ProofStatus::IN_PROGRESS);

        $totalProofs = (int) $totalProofsQuery->getQuery()->getSingleScalarResult();

        return $this->renderResponse(
            '@VideoGamesRecordsProof/admin/block/proofs_by_game.html.twig',
            [
                'block' => $blockContext->getBlock(),
                'settings' => $settings,
                'games' => $games,
                'totalProofs' => $totalProofs,
            ],
            $response
        );
    }
}
