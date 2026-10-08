<?php

declare(strict_types=1);

namespace App\SharedKernel\Application\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:geoip:update',
    description: 'Download the latest MaxMind GeoLite2-Country database',
)]
class GeoIpUpdateCommand extends Command
{
    private const DOWNLOAD_URL = 'https://download.maxmind.com/app/geoip_download';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly Filesystem $filesystem,
        #[Autowire('%env(MAXMIND_LICENSE_KEY)%')]
        private readonly string $licenseKey,
        #[Autowire('%app.geoip.database_path%')]
        private readonly string $databasePath,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $tmpDir = sys_get_temp_dir() . '/geoip-' . uniqid();
        $archivePath = $tmpDir . '/GeoLite2-Country.tar.gz';
        $this->filesystem->mkdir($tmpDir);

        try {
            $io->info('Downloading GeoLite2-Country database from MaxMind...');
            $response = $this->httpClient->request('GET', self::DOWNLOAD_URL, [
                'query' => [
                    'edition_id' => 'GeoLite2-Country',
                    'license_key' => $this->licenseKey,
                    'suffix' => 'tar.gz',
                ],
            ]);

            if (200 !== $response->getStatusCode()) {
                $io->error(sprintf('MaxMind responded with HTTP %d', $response->getStatusCode()));

                return Command::FAILURE;
            }

            $this->filesystem->dumpFile($archivePath, $response->getContent());

            $io->info('Extracting archive...');
            $archive = new \PharData($archivePath);
            $archive->extractTo($tmpDir);

            $mmdbFiles = glob($tmpDir . '/*/GeoLite2-Country.mmdb');
            if (empty($mmdbFiles)) {
                $io->error('GeoLite2-Country.mmdb not found in the downloaded archive.');

                return Command::FAILURE;
            }

            $this->filesystem->mkdir(dirname($this->databasePath));
            $this->filesystem->copy($mmdbFiles[0], $this->databasePath, true);

            $io->success(sprintf('GeoLite2-Country database updated at: %s', $this->databasePath));

            return Command::SUCCESS;
        } finally {
            $this->filesystem->remove($tmpDir);
        }
    }
}
