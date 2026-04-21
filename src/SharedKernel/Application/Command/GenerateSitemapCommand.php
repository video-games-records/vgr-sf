<?php

declare(strict_types=1);

namespace App\SharedKernel\Application\Command;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\PlayerRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\CountryRepository;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\SerieRepository;
use App\BoundedContext\Forum\Infrastructure\Doctrine\Repository\ForumRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:generate-sitemap',
    description: 'Generate sitemap.xml file',
)]
class GenerateSitemapCommand extends Command
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly PlayerRepository $playerRepository,
        private readonly CountryRepository $countryRepository,
        private readonly SerieRepository $serieRepository,
        private readonly ForumRepository $forumRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $urlset = $xml->createElement('urlset');
        $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $xml->appendChild($urlset);

        // Static pages
        $staticPages = [
            ['route' => 'home', 'params' => ['_locale' => 'en'], 'priority' => '1.0', 'changefreq' => 'daily'],
            ['route' => 'home', 'params' => ['_locale' => 'fr'], 'priority' => '1.0', 'changefreq' => 'daily'],
        ];

        foreach ($staticPages as $page) {
            $this->addUrl(
                $xml,
                $urlset,
                $this->urlGenerator->generate($page['route'], $page['params'] ?? [], UrlGeneratorInterface::ABSOLUTE_URL),
                $page['priority'],
                $page['changefreq']
            );
        }

        // Games
        $io->info('Adding games to sitemap...');
        $games = $this->gameRepository->findAll();
        foreach ($games as $game) {
            $this->addUrl(
                $xml,
                $urlset,
                $this->urlGenerator->generate(
                    'vgr_game_show',
                    ['_locale' => 'en', 'id' => $game->getId(), 'slug' => $game->getSlug()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                '0.8',
                'weekly'
            );
        }

        // TODO: Add other sections when routes are available
        // - Players
        // - Series
        // - Countries
        // - Forums

        // Save sitemap
        $sitemapPath = $this->projectDir . '/public/sitemap.xml';
        $xml->save($sitemapPath);

        $io->success(sprintf(
            'Sitemap generated successfully with %d URLs at: %s',
            $urlset->childNodes->length,
            $sitemapPath
        ));

        return Command::SUCCESS;
    }

    private function addUrl(
        \DOMDocument $xml,
        \DOMElement $urlset,
        string $loc,
        string $priority,
        string $changefreq
    ): void {
        $url = $xml->createElement('url');

        $locElement = $xml->createElement('loc', htmlspecialchars($loc));
        $url->appendChild($locElement);

        $lastmodElement = $xml->createElement('lastmod', date('Y-m-d'));
        $url->appendChild($lastmodElement);

        $changefreqElement = $xml->createElement('changefreq', $changefreq);
        $url->appendChild($changefreqElement);

        $priorityElement = $xml->createElement('priority', $priority);
        $url->appendChild($priorityElement);

        $urlset->appendChild($url);
    }
}
