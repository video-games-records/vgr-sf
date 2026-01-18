<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Infrastructure\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\HttpFoundation\RequestStack;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Rule;

#[AsEntityListener(event: Events::postLoad, method: 'postLoad', entity: Rule::class)]
class RuleListener
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    /**
     * @param Rule $rule
     */
    public function postLoad(Rule $rule): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $rule->setCurrentLocale($request->getLocale());
        }
    }
}
