<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Igdb\Presentation\Admin;

use App\SharedKernel\Presentation\Admin\BaseAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;

final class PlatformTypeAdmin extends BaseAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('create')
            ->remove('edit')
            ->remove('delete')
            ->remove('export');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        // Read-only admin - no form fields needed as data comes from IGDB API
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id', null, ['label' => 'field.id'])
            ->add('name', null, ['label' => 'field.name']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, ['label' => 'field.id'])
            ->add('name', null, ['label' => 'field.name'])
            ->add('createdAt', null, ['label' => 'field.created_at'])
            ->add('updatedAt', null, ['label' => 'field.updated_at'])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => ['template' => null],
                ],
            ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id', null, ['label' => 'field.id'])
            ->add('name', null, ['label' => 'field.name'])
            ->add('checksum', null, ['label' => 'field.checksum'])
            ->add('createdAt', null, ['label' => 'field.created_at'])
            ->add('updatedAt', null, ['label' => 'field.updated_at']);
    }
}
