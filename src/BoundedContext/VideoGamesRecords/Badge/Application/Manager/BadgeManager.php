<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Badge\Application\Manager;

use Doctrine\ORM\EntityManagerInterface;
use App\BoundedContext\VideoGamesRecords\Badge\Domain\Entity\Badge;

readonly class BadgeManager
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function updatePicture(Badge $badge, string $filename): void
    {
        $badge->setPicture($filename);
        $this->em->flush();
    }
}
