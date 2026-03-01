<?php

declare(strict_types=1);

namespace App\SharedKernel\Presentation\Twig\Extension;

use DH\Auditor\Model\Entry;
use DH\Auditor\Provider\Doctrine\Persistence\Reader\Reader;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AuditHistoryExtension extends AbstractExtension
{
    public function __construct(
        private readonly Reader $reader,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('vgr_audit_history', [$this, 'getAuditHistory']),
        ];
    }

    /**
     * @return Entry[]
     */
    public function getAuditHistory(string $entityClass, int $id, int $limit = 10): array
    {
        try {
            $query = $this->reader->createQuery($entityClass, [
                'object_id' => $id,
                'page_size' => $limit,
                'page' => 1,
            ]);

            return $query->execute();
        } catch (\Throwable) {
            return [];
        }
    }
}
