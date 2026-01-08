<?php

namespace App\SharedKernel\Domain\Interface;

interface RepositoryInterface
{
    public function save(object $entity): void;

    public function findById(int $id): ?object;

    /**
     * @return object[]
     */
    public function findAll(): array;

    public function remove(object $entity): void;

    public function flush(): void;
}
