<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Igdb\Presentation\Admin;

use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\PlatformType;
use App\SharedKernel\Presentation\Admin\BaseAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

final class PlatformAdmin extends BaseAdmin
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
            ->add('name', null, ['label' => 'field.platform.name'])
            ->add('abbreviation', null, ['label' => 'field.platform.abbreviation'])
            ->add('generation', null, ['label' => 'field.platform.generation'])
            ->add('slug', null, ['label' => 'field.slug'])
            ->add('platformType', ModelFilter::class, [
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => PlatformType::class,
                    'choice_label' => 'name',
                ],
                'label' => 'field.platform.platform_type',
            ]);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, ['label' => 'field.id'])
            ->add('name', null, ['label' => 'field.platform.name'])
            ->add('abbreviation', null, ['label' => 'field.platform.abbreviation'])
            ->add('generation', null, ['label' => 'field.platform.generation'])
            ->add('platformType.name', null, ['label' => 'field.platform.platform_type'])
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
            ->add('name', null, ['label' => 'field.platform.name'])
            ->add('abbreviation', null, ['label' => 'field.platform.abbreviation'])
            ->add('alternativeName', null, ['label' => 'field.platform.alternative_name'])
            ->add('generation', null, ['label' => 'field.platform.generation'])
            ->add('slug', null, ['label' => 'field.slug'])
            ->add('summary', null, ['label' => 'field.platform.summary'])
            ->add('url', null, ['label' => 'field.url'])
            ->add('checksum', null, ['label' => 'field.checksum'])
            ->add('platformType.name', null, ['label' => 'field.platform.platform_type'])
            ->add('platformLogo.imageId', null, ['label' => 'field.platform.platform_logo'])
            ->add('createdAt', null, ['label' => 'field.created_at'])
            ->add('updatedAt', null, ['label' => 'field.updated_at']);
    }
}
