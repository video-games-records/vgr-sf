<?php

declare(strict_types=1);

namespace App\SharedKernel\Application\Command;

use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\Repository\GameRepository;
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
    private const SUPPORTED_LOCALES = ['en', 'fr', 'de', 'it', 'ja', 'es', 'pt_BR', 'zh_CN'];

    public function __construct(
        private readonly GameRepository $gameRepository,
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
        $urlset->setAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
        $xml->appendChild($urlset);

        // Static pages - Homepage for all locales
        $io->info('Adding homepage for all locales...');
        $this->addMultilingualUrl(
            $xml,
            $urlset,
            'home',
            [],
            '1.0',
            'daily'
        );

        // Games
        $io->info('Adding games to sitemap...');
        $games = $this->gameRepository->findAll();
        foreach ($games as $game) {
            $this->addMultilingualUrl(
                $xml,
                $urlset,
                'vgr_game_show',
                ['id' => $game->getId(), 'slug' => $game->getSlug()],
                '0.8',
                'weekly'
            );
        }

        // TODO: Add other sections when routes are available

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

    /**
     * Add URLs for all supported locales with hreflang attributes
     */
    private function addMultilingualUrl(
        \DOMDocument $xml,
        \DOMElement $urlset,
        string $route,
        array $routeParams,
        string $priority,
        string $changefreq
    ): void {
        foreach (self::SUPPORTED_LOCALES as $locale) {
            $url = $xml->createElement('url');

            // Generate main URL for this locale
            $params = array_merge($routeParams, ['_locale' => $locale]);
            $mainUrl = $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
            
            $locElement = $xml->createElement('loc', htmlspecialchars($mainUrl));
            $url->appendChild($locElement);

            $lastmodElement = $xml->createElement('lastmod', date('Y-m-d'));
            $url->appendChild($lastmodElement);

            $changefreqElement = $xml->createElement('changefreq', $changefreq);
            $url->appendChild($changefreqElement);

            $priorityElement = $xml->createElement('priority', $priority);
            $url->appendChild($priorityElement);

            // Add hreflang links for all other locales
            foreach (self::SUPPORTED_LOCALES as $hreflangLocale) {
                $hreflangParams = array_merge($routeParams, ['_locale' => $hreflangLocale]);
                $hreflangUrl = $this->urlGenerator->generate($route, $hreflangParams, UrlGeneratorInterface::ABSOLUTE_URL);
                
                $linkElement = $xml->createElementNS('http://www.w3.org/1999/xhtml', 'xhtml:link');
                $linkElement->setAttribute('rel', 'alternate');
                $linkElement->setAttribute('hreflang', $this->formatHreflangCode($hreflangLocale));
                $linkElement->setAttribute('href', htmlspecialchars($hreflangUrl));
                $url->appendChild($linkElement);
            }

            $urlset->appendChild($url);
        }
    }

    /**
     * Convert locale code to proper hreflang format
     */
    private function formatHreflangCode(string $locale): string
    {
        return match($locale) {
            'pt_BR' => 'pt-BR',
            'zh_CN' => 'zh-CN',
            default => $locale
        };
    }
}
