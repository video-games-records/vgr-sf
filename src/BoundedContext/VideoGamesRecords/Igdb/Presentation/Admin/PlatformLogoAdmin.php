<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Igdb\Presentation\Admin;

use App\SharedKernel\Presentation\Admin\BaseAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;

final class PlatformLogoAdmin extends BaseAdmin
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
            ->add('imageId', null, ['label' => 'field.platform_logo.image_id'])
            ->add('alphaChannel', null, ['label' => 'field.platform_logo.alpha_channel'])
            ->add('animated', null, ['label' => 'field.platform_logo.animated'])
            ->add('width', null, ['label' => 'field.platform_logo.width'])
            ->add('height', null, ['label' => 'field.platform_logo.height']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, ['label' => 'field.id'])
            ->add('imageId', null, ['label' => 'field.platform_logo.image_id'])
            ->add('width', null, ['label' => 'field.platform_logo.width'])
            ->add('height', null, ['label' => 'field.platform_logo.height'])
            ->add('alphaChannel', null, ['label' => 'field.platform_logo.alpha_channel'])
            ->add('animated', null, ['label' => 'field.platform_logo.animated'])
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
            ->add('alphaChannel', null, ['label' => 'field.platform_logo.alpha_channel'])
            ->add('animated', null, ['label' => 'field.platform_logo.animated'])
            ->add('checksum', null, ['label' => 'field.checksum'])
            ->add('height', null, ['label' => 'field.platform_logo.height'])
            ->add('imageId', null, ['label' => 'field.platform_logo.image_id'])
            ->add('url', null, ['label' => 'field.platform_logo.url'])
            ->add('width', null, ['label' => 'field.platform_logo.width'])
            ->add('createdAt', null, ['label' => 'field.created_at'])
            ->add('updatedAt', null, ['label' => 'field.updated_at']);
    }
}
