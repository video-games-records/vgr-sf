<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Twig\Extension;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Group;
use Doctrine\Common\Collections\Collection;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GroupExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('vgr_sort_groups_by_name', [$this, 'sortByName']),
        ];
    }

    /**
     * @param Collection<int, Group>|Group[] $groups
     * @return Group[]
     */
    public function sortByName(Collection|array $groups, string $locale): array
    {
        $groups = $groups instanceof Collection ? $groups->toArray() : $groups;

        usort($groups, static fn (Group $a, Group $b) => strcasecmp(
            $locale === 'fr' ? $a->getLibGroupFr() : $a->getLibGroupEn(),
            $locale === 'fr' ? $b->getLibGroupFr() : $b->getLibGroupEn()
        ));

        return $groups;
    }
}
