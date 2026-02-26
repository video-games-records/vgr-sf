<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Dwh\Application\DataProvider\Top;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\BoundedContext\VideoGamesRecords\Dwh\Domain\Contract\DataProvider\TopProviderInterface;

class AbstractTopProvider implements TopProviderInterface
{
    protected EntityManagerInterface $dwhEntityManager;
    protected EntityManagerInterface $defaultEntityManager;

    public function __construct(EntityManagerInterface $dwhEntityManager, EntityManagerInterface $defaultEntityManager)
    {
        $this->dwhEntityManager = $dwhEntityManager;
        $this->defaultEntityManager = $defaultEntityManager;
    }


    /** @return array<mixed> */
    public function getTop(
        DateTime $date1Begin,
        DateTime $date1End,
        DateTime $date2Begin,
        DateTime $date2End,
        int $limit = 20
    ): array {
        return [];
    }
}
