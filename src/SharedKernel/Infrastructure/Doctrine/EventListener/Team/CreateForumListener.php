<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\Doctrine\EventListener\Team;

use App\BoundedContext\Forum\Domain\Entity\Forum;
use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, method: 'setForum', entity: Team::class)]
readonly class CreateForumListener
{
    public const int CATEGORY_ID = 9;

    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function setForum(Team $team): void
    {
        $category = $this->em->getRepository('App\BoundedContext\Forum\Domain\Entity\Category')
            ->findOneBy(['id' => self::CATEGORY_ID]);

        $forum = new Forum();
        $forum->setLibForum($team->getLibTeam());
        $forum->setLibForumFr($team->getLibTeam());
        $forum->setCategory($category);

        $team->setForum($forum);
    }
}
