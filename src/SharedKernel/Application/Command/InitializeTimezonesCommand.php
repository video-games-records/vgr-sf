<?php

declare(strict_types=1);

namespace App\SharedKernel\Application\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:initialize-timezones',
    description: 'Initialize user timezones based on their country'
)]
class InitializeTimezonesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Run in dry-run mode (no database updates)')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force update even if user already has a timezone set')
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Process users in batches (useful for very large datasets)', '0');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');
        $force = $input->getOption('force');
        $batchSize = (int) $input->getOption('batch-size');

        $io->title('Initialize User Timezones from Country');

        if ($dryRun) {
            $io->note('Running in DRY-RUN mode - no changes will be made');
        }

        // Get country to timezone mapping
        $countryTimezoneMap = $this->getCountryTimezoneMapping();

        // Use raw SQL for much better performance
        $conn = $this->entityManager->getConnection();

        // First, let's count how many users will be updated
        $countSql = '
            SELECT COUNT(DISTINCT u.id) as total
            FROM pnu_user u
            INNER JOIN vgr_player p ON p.user_id = u.id
            INNER JOIN vgr_country c ON p.country_id = c.id
            WHERE c.code_iso2 IS NOT NULL
        ';

        if (!$force) {
            $countSql .= " AND (u.timezone IS NULL OR u.timezone = 'UTC')";
        }

        $totalCount = $conn->executeQuery($countSql)->fetchOne();

        if ($totalCount == 0) {
            $io->success('No users found that need timezone initialization');
            return Command::SUCCESS;
        }

        $io->text(sprintf('Found %d users to process', $totalCount));

        $updated = 0;
        $skipped = 0;

        // Check if batch processing is requested
        if ($batchSize > 0 && !$dryRun) {
            return $this->executeBatchUpdate($io, $conn, $countryTimezoneMap, $force, $batchSize, $totalCount);
        }

        if (!$dryRun) {
            // Build CASE statement for bulk update
            $whenClauses = [];
            foreach ($countryTimezoneMap as $countryCode => $timezone) {
                $whenClauses[] = sprintf(
                    "WHEN c.code_iso2 = %s THEN %s",
                    $conn->quote($countryCode),
                    $conn->quote($timezone)
                );
            }

            if (!empty($whenClauses)) {
                $updateSql = "
                    UPDATE pnu_user u
                    INNER JOIN vgr_player p ON p.user_id = u.id
                    INNER JOIN vgr_country c ON p.country_id = c.id
                    SET u.timezone = CASE " . implode(' ', $whenClauses) . " 
                        ELSE u.timezone 
                    END
                    WHERE c.code_iso2 IS NOT NULL
                ";

                if (!$force) {
                    $updateSql .= " AND (u.timezone IS NULL OR u.timezone = 'UTC')";
                }

                $io->text('Executing bulk update...');

                try {
                    $startTime = microtime(true);
                    $affectedRows = $conn->executeStatement($updateSql);
                    $executionTime = microtime(true) - $startTime;

                    $updated = $affectedRows;
                    $io->text(sprintf('Update completed in %.2f seconds', $executionTime));
                } catch (\Exception $e) {
                    $io->error('Failed to update users: ' . $e->getMessage());
                    return Command::FAILURE;
                }
            }
        } else {
            // For dry-run, just simulate the count
            $selectSql = "
                SELECT COUNT(DISTINCT u.id) as will_update
                FROM pnu_user u
                INNER JOIN vgr_player p ON p.user_id = u.id
                INNER JOIN vgr_country c ON p.country_id = c.id
                WHERE c.code_iso2 IS NOT NULL
                AND c.code_iso2 IN ('" . implode("','", array_keys($countryTimezoneMap)) . "')
            ";

            if (!$force) {
                $selectSql .= " AND (u.timezone IS NULL OR u.timezone = 'UTC')";
            }

            $updated = $conn->executeQuery($selectSql)->fetchOne();
            $skipped = $totalCount - $updated;
        }

        // Get some statistics for verbose mode
        if ($io->isVerbose()) {
            $statsSql = "
                SELECT c.code_iso2 as country_code, COUNT(u.id) as user_count
                FROM pnu_user u
                INNER JOIN vgr_player p ON p.user_id = u.id
                INNER JOIN vgr_country c ON p.country_id = c.id
                WHERE c.code_iso2 IS NOT NULL
                GROUP BY c.code_iso2
                ORDER BY user_count DESC
                LIMIT 10
            ";

            $stats = $conn->executeQuery($statsSql)->fetchAllAssociative();

            $io->section('Top 10 countries by user count:');
            foreach ($stats as $stat) {
                $timezone = $countryTimezoneMap[$stat['country_code']] ?? 'Not mapped';
                $io->text(sprintf(
                    '  %s: %d users -> %s',
                    $stat['country_code'],
                    $stat['user_count'],
                    $timezone
                ));
            }
        }

        $io->section('Results');
        $io->table(
            ['Status', 'Count'],
            [
                ['Updated', $updated],
                ['Skipped', $totalCount - $updated],
                ['Total processed', $totalCount]
            ]
        );

        if ($dryRun) {
            $io->note('This was a dry-run. Run without --dry-run to apply changes.');
        } else {
            $io->success(sprintf('Timezone initialization completed! Updated %d users.', $updated));
        }

        return Command::SUCCESS;
    }

    /**
     * @param array<string, string> $countryTimezoneMap
     */
    private function executeBatchUpdate(
        SymfonyStyle $io,
        \Doctrine\DBAL\Connection $conn,
        array $countryTimezoneMap,
        bool $force,
        int $batchSize,
        int $totalCount
    ): int {
        $io->text(sprintf('Processing %d users in batches of %d...', $totalCount, $batchSize));

        $offset = 0;
        $totalUpdated = 0;
        $io->progressStart($totalCount);

        while ($offset < $totalCount) {
            // Get user IDs for this batch
            $batchSql = "
                SELECT DISTINCT u.id
                FROM pnu_user u
                INNER JOIN vgr_player p ON p.user_id = u.id
                INNER JOIN vgr_country c ON p.country_id = c.id
                WHERE c.code_iso2 IS NOT NULL
            ";

            if (!$force) {
                $batchSql .= " AND (u.timezone IS NULL OR u.timezone = 'UTC')";
            }

            $batchSql .= sprintf(" LIMIT %d OFFSET %d", $batchSize, $offset);

            $userIds = $conn->executeQuery($batchSql)->fetchFirstColumn();

            if (empty($userIds)) {
                break;
            }

            // Build CASE statement for this batch
            $whenClauses = [];
            foreach ($countryTimezoneMap as $countryCode => $timezone) {
                $whenClauses[] = sprintf(
                    "WHEN c.code_iso2 = %s THEN %s",
                    $conn->quote($countryCode),
                    $conn->quote($timezone)
                );
            }

            if (!empty($whenClauses)) {
                $updateSql = "
                    UPDATE pnu_user u
                    INNER JOIN vgr_player p ON p.user_id = u.id
                    INNER JOIN vgr_country c ON p.country_id = c.id
                    SET u.timezone = CASE " . implode(' ', $whenClauses) . " 
                        ELSE u.timezone 
                    END
                    WHERE u.id IN (" . implode(',', $userIds) . ")
                    AND c.code_iso2 IS NOT NULL
                ";

                try {
                    $affectedRows = $conn->executeStatement($updateSql);
                    $totalUpdated += $affectedRows;
                    $io->progressAdvance(count($userIds));
                } catch (\Exception $e) {
                    $io->error('Failed to update batch: ' . $e->getMessage());
                    return Command::FAILURE;
                }
            }

            $offset += $batchSize;
        }

        $io->progressFinish();

        $io->section('Results');
        $io->table(
            ['Status', 'Count'],
            [
                ['Updated', $totalUpdated],
                ['Total processed', $totalCount]
            ]
        );

        $io->success(sprintf('Timezone initialization completed! Updated %d users in batches.', $totalUpdated));

        return Command::SUCCESS;
    }

    /**
     * @return array<string, string>
     */
    private function getCountryTimezoneMapping(): array
    {
        return [
            // Europe
            'FR' => 'Europe/Paris',
            'DE' => 'Europe/Berlin',
            'IT' => 'Europe/Rome',
            'ES' => 'Europe/Madrid',
            'GB' => 'Europe/London',
            'NL' => 'Europe/Amsterdam',
            'BE' => 'Europe/Brussels',
            'CH' => 'Europe/Zurich',
            'AT' => 'Europe/Vienna',
            'PT' => 'Europe/Lisbon',
            'SE' => 'Europe/Stockholm',
            'NO' => 'Europe/Oslo',
            'DK' => 'Europe/Copenhagen',
            'FI' => 'Europe/Helsinki',
            'PL' => 'Europe/Warsaw',
            'CZ' => 'Europe/Prague',
            'HU' => 'Europe/Budapest',
            'RO' => 'Europe/Bucharest',
            'GR' => 'Europe/Athens',
            'RU' => 'Europe/Moscow',
            'UA' => 'Europe/Kiev',
            'TR' => 'Europe/Istanbul',

            // North America
            'US' => 'America/New_York', // Eastern by default
            'CA' => 'America/Toronto',  // Eastern by default
            'MX' => 'America/Mexico_City',

            // South America
            'BR' => 'America/Sao_Paulo',
            'AR' => 'America/Buenos_Aires',
            'CL' => 'America/Santiago',
            'CO' => 'America/Bogota',
            'PE' => 'America/Lima',
            'VE' => 'America/Caracas',

            // Asia
            'JP' => 'Asia/Tokyo',
            'CN' => 'Asia/Shanghai',
            'KR' => 'Asia/Seoul',
            'IN' => 'Asia/Kolkata',
            'TH' => 'Asia/Bangkok',
            'VN' => 'Asia/Ho_Chi_Minh',
            'SG' => 'Asia/Singapore',
            'HK' => 'Asia/Hong_Kong',
            'TW' => 'Asia/Taipei',
            'MY' => 'Asia/Kuala_Lumpur',
            'ID' => 'Asia/Jakarta',
            'PH' => 'Asia/Manila',
            'AE' => 'Asia/Dubai',
            'SA' => 'Asia/Riyadh',
            'IL' => 'Asia/Jerusalem',

            // Oceania
            'AU' => 'Australia/Sydney',
            'NZ' => 'Pacific/Auckland',

            // Africa
            'ZA' => 'Africa/Johannesburg',
            'EG' => 'Africa/Cairo',
            'NG' => 'Africa/Lagos',
            'KE' => 'Africa/Nairobi',
            'MA' => 'Africa/Casablanca',
            'TN' => 'Africa/Tunis',
            'DZ' => 'Africa/Algiers',

            // Others (fallback to UTC if not mapped)
        ];
    }
}
