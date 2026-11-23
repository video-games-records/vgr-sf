<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Shared\Presentation\Twig\Components;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('pagination')]
class Pagination
{
    public int $currentPage;
    public int $totalPages;
    public string $route;
    /** @var array<string, mixed> */
    public array $routeParams = [];

    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function url(int $page): string
    {
        // 'page' on the left so it always overrides any 'page' already present in routeParams.
        return $this->urlGenerator->generate($this->route, ['page' => $page] + $this->routeParams);
    }
}
