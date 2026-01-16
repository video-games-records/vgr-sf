<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\RequestStack;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Rule;
use App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Security\UserProvider;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Rule::class)]
#[AsEntityListener(event: Events::postLoad, method: 'postLoad', entity: Rule::class)]
class RuleListener
{
    public function __construct(private UserProvider $userProvider, private RequestStack $requestStack)
    {
    }


    /**
     * @param Rule $rule
     * @param LifecycleEventArgs $event
     * @return void
     * @throws ORMException
     */
    public function prePersist(Rule $rule, LifecycleEventArgs $event): void
    {
        $rule->setPlayer($this->userProvider->getPlayer());
    }

    /**
     * @param Rule $rule
     * @param LifecycleEventArgs $event
     */
    public function postLoad(Rule $rule, LifecycleEventArgs $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $rule->setCurrentLocale($request->getLocale());
        }
    }
}
