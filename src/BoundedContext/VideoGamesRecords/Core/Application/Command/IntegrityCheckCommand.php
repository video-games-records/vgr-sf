<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Application\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'vgr:player-chart:integrity-check',
    description: 'Check player_chart/proof integrity and send results by email'
)]
class IntegrityCheckCommand extends Command
{
    private const string ADMIN_EMAIL = 'magicbart@gmail.com';

    private const array CHECKS = [
        'proof-sent sans proof_id' => <<<SQL
            SELECT COUNT(*) AS nb FROM vgr_player_chart
            WHERE status IN ('proof-sent', 'request-proof-sent') AND proof_id IS NULL
            SQL,
        'proof-sent avec FK cassée' => <<<SQL
            SELECT COUNT(*) AS nb FROM vgr_player_chart pc
            LEFT JOIN vgr_proof p ON p.id = pc.proof_id
            WHERE pc.status IN ('proof-sent', 'request-proof-sent')
              AND pc.proof_id IS NOT NULL AND p.id IS NULL
            SQL,
        'proved avec FK cassée' => <<<SQL
            SELECT COUNT(*) AS nb FROM vgr_player_chart pc
            LEFT JOIN vgr_proof p ON p.id = pc.proof_id
            WHERE pc.status = 'proved' AND pc.proof_id IS NOT NULL AND p.id IS NULL
            SQL,
        'proved mais preuve non ACCEPTED' => <<<SQL
            SELECT COUNT(*) AS nb FROM vgr_player_chart pc
            INNER JOIN vgr_proof p ON p.id = pc.proof_id
            WHERE pc.status = 'proved' AND p.status <> 'ACCEPTED'
            SQL,
        'proof-sent avec preuve ACCEPTED' => <<<SQL
            SELECT COUNT(*) AS nb FROM vgr_player_chart pc
            INNER JOIN vgr_proof p ON p.id = pc.proof_id
            WHERE pc.status IN ('proof-sent', 'request-proof-sent') AND p.status = 'ACCEPTED'
            SQL,
        'proof-sent avec preuve REFUSED/CLOSED' => <<<SQL
            SELECT COUNT(*) AS nb FROM vgr_player_chart pc
            INNER JOIN vgr_proof p ON p.id = pc.proof_id
            WHERE pc.status IN ('proof-sent', 'request-proof-sent') AND p.status IN ('REFUSED', 'CLOSED')
            SQL,
        'proof_id sur statut sans preuve' => <<<SQL
            SELECT COUNT(*) AS nb FROM vgr_player_chart
            WHERE status IN ('none', 'request-pending', 'request-validated', 'unproved')
              AND proof_id IS NOT NULL
            SQL,
        'preuve DELETED liée à un score' => <<<SQL
            SELECT COUNT(*) AS nb FROM vgr_player_chart pc
            INNER JOIN vgr_proof p ON p.id = pc.proof_id
            WHERE p.status = 'DELETED'
            SQL,
        'preuve IN PROGRESS orpheline' => <<<SQL
            SELECT COUNT(*) AS nb FROM vgr_proof p
            LEFT JOIN vgr_player_chart pc ON pc.proof_id = p.id
            WHERE p.status = 'IN PROGRESS' AND pc.id IS NULL
            SQL,
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly MailerInterface $mailer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Player-chart integrity check');

        $results = [];
        $totalAnomalies = 0;

        foreach (self::CHECKS as $label => $sql) {
            $nb = (int) $this->connection->fetchOne($sql);
            $results[$label] = $nb;
            $totalAnomalies += $nb;
            $io->text(sprintf('%-45s %d', $label, $nb));
        }

        $this->sendReport($results, $totalAnomalies);

        $io->success(sprintf('Report sent to %s (%d anomalie(s))', self::ADMIN_EMAIL, $totalAnomalies));

        return Command::SUCCESS;
    }

    /** @param array<string, int> $results */
    private function sendReport(array $results, int $totalAnomalies): void
    {
        $date = (new \DateTimeImmutable())->format('d/m/Y');
        $statusLabel = $totalAnomalies === 0 ? '✅ OK' : sprintf('⚠️ %d anomalie(s)', $totalAnomalies);
        $subject = sprintf('[VGR] Intégrité player_chart — %s — %s', $date, $statusLabel);

        $rows = '';
        foreach ($results as $label => $nb) {
            $color = $nb > 0 ? '#c0392b' : '#27ae60';
            $rows .= sprintf(
                '<tr><td style="padding:6px 12px;border-bottom:1px solid #eee;">%s</td><td style="padding:6px 12px;border-bottom:1px solid #eee;text-align:right;font-weight:bold;color:%s;">%d</td></tr>',
                htmlspecialchars($label),
                $color,
                $nb
            );
        }

        $html = <<<HTML
            <html><body style="font-family:sans-serif;color:#333;">
            <h2>Contrôle intégrité vgr_player_chart</h2>
            <p>Date : {$date} &nbsp;|&nbsp; <strong>{$statusLabel}</strong></p>
            <table style="border-collapse:collapse;min-width:400px;">
                <thead>
                    <tr style="background:#f0f0f0;">
                        <th style="padding:6px 12px;text-align:left;">Anomalie</th>
                        <th style="padding:6px 12px;text-align:right;">Nb</th>
                    </tr>
                </thead>
                <tbody>{$rows}</tbody>
            </table>
            </body></html>
            HTML;

        $email = (new Email())
            ->to(self::ADMIN_EMAIL)
            ->subject($subject)
            ->html($html);

        $this->mailer->send($email);
    }
}
