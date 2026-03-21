<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Application\Command;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'vgr:badge:update-simple',
    description: 'Assign simple quantity-based badges (Connexion, VgrChart, VgrProof) to players'
)]
class UpdateSimpleBadgesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Update Simple Badges');

        $sql = "INSERT INTO vgr_player_badge (player_id, badge_id, created_at, updated_at)
            SELECT vgr_player.id, vgr_badge.id, NOW(), NOW()
            FROM vgr_player, vgr_badge
            WHERE type = :type
            AND value <= vgr_player.%s
            AND vgr_badge.id NOT IN (SELECT badge_id FROM vgr_player_badge WHERE player_id = vgr_player.id)";

        $types = [
            'Connexion' => 'nb_connexion',
            'VgrChart'  => 'nb_chart',
            'VgrProof'  => 'nb_chart_proven',
        ];

        foreach ($types as $type => $column) {
            $count = $this->em->getConnection()->executeStatement(
                sprintf($sql, $column),
                ['type' => $type]
            );
            $io->writeln(sprintf('<info>%s</info>: %d badge(s) assigned', $type, $count));
        }

        $io->success('Simple badges updated successfully.');

        return Command::SUCCESS;
    }
}
