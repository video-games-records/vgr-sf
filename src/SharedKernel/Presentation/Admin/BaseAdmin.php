<?php

declare(strict_types=1);

namespace App\SharedKernel\Presentation\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use App\SharedKernel\Domain\Contracts\SecurityInterface;
use App\SharedKernel\Domain\Traits\Accessor\SetSecurity;
use App\SharedKernel\Domain\Traits\Accessor\SetRequestStack;
use App\SharedKernel\Domain\Traits\Accessor\SetEventDispatcher;
use Sonata\DoctrineORMAdminBundle\Model\ModelManager;

/**
 * @phpstan-template T of object
 * @phpstan-extends AbstractAdmin<T>
 */
abstract class BaseAdmin extends AbstractAdmin implements SecurityInterface
{
    use SetSecurity;
    use SetRequestStack;
    use SetEventDispatcher;
}
