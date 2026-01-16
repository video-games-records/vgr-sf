<?php

declare(strict_types=1);

namespace App\SharedKernel\Presentation\Web\Controller\Admin;

use App\SharedKernel\Domain\Contracts\SecurityInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\DoctrineORMAdminBundle\Model\ModelManager;

/**
 * @phpstan-template T of object
 * @phpstan-extends CRUDController<T>
 */
class AbstractCRUDController extends CRUDController implements SecurityInterface
{
    public function getEntityManager(): EntityManagerInterface
    {
        $modelManager = $this->admin->getModelManager();
        assert($modelManager instanceof ModelManager);
        return $modelManager->getEntityManager($this->admin->getClass());
    }
}
