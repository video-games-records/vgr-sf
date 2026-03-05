<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Infrastructure\Doctrine\EventListener;

use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use HTMLPurifier;
use HTMLPurifier_Config;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Team::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Team::class)]
class TeamListener
{
    private HTMLPurifier $purifier;

    public function __construct()
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,br,strong,em,u,ol,ul,li,a[href],h1,h2,h3,blockquote');
        $this->purifier = new HTMLPurifier($config);
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
            $team->setPresentation($this->purifier->purify($team->getPresentation()));
        }
    }
}
