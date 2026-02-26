<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Application\DataProvider\Core;

use App\BoundedContext\VideoGamesRecords\Dwh\Domain\Contract\DataProvider\CoreDataProviderInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class AbstractCoreDataProvider implements CoreDataProviderInterface
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /** @return array<mixed> */
    public function getData(): array
    {
        return [];
    }

    /** @return array<int, int> */
    public function getNbPostDay(DateTime $date1, DateTime $date2): array
    {
        return [];
    }
}
