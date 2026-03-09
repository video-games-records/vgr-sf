<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Team::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Team::class)]
class TeamListener
{
    public function __construct(
        #[Autowire(service: 'html_sanitizer.sanitizer.app.content_sanitizer')]
        private readonly HtmlSanitizerInterface $sanitizer,
    ) {
    }

    public function prePersist(Team $team): void
    {
        $this->purifyPresentation($team);
    }

    public function preUpdate(Team $team): void
    {
        $this->purifyPresentation($team);
    }

    private function purifyPresentation(Team $team): void
    {
        if ($team->getPresentation()) {
            $team->setPresentation($this->sanitizer->sanitize($team->getPresentation()));
        }
    }
}
