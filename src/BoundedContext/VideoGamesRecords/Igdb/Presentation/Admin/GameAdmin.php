<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Igdb\Presentation\Admin;

use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\Game;
use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\Genre;
use App\BoundedContext\VideoGamesRecords\Igdb\Domain\Entity\Platform;
use App\SharedKernel\Presentation\Admin\BaseAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

final class GameAdmin extends BaseAdmin
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
            ->add('name', null, ['label' => 'field.game.name'])
            ->add('slug', null, ['label' => 'field.slug'])
            ->add('firstReleaseDate', null, ['label' => 'field.game.first_release_date'])
            ->add('versionParent', ModelFilter::class, [
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => Game::class,
                    'choice_label' => 'name',
                ],
                'label' => 'field.game.version_parent',
            ])
            ->add('genres', ModelFilter::class, [
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => Genre::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                ],
                'label' => 'field.game.genres',
            ])
            ->add('platforms', ModelFilter::class, [
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => Platform::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                ],
                'label' => 'field.game.platforms',
            ]);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, ['label' => 'field.id'])
            ->add('name', null, ['label' => 'field.game.name'])
            ->add('slug', null, ['label' => 'field.slug'])
            ->add('firstReleaseDate', null, ['label' => 'field.game.first_release_date'])
            ->add('versionParent.name', null, ['label' => 'field.game.version_parent'])
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
            ->add('name', null, ['label' => 'field.game.name'])
            ->add('slug', null, ['label' => 'field.slug'])
            ->add('storyline', null, ['label' => 'field.game.storyline'])
            ->add('summary', null, ['label' => 'field.game.summary'])
            ->add('url', null, ['label' => 'field.url'])
            ->add('checksum', null, ['label' => 'field.checksum'])
            ->add('firstReleaseDate', null, ['label' => 'field.game.first_release_date'])
            ->add('versionParent.name', null, ['label' => 'field.game.version_parent'])
            ->add('genres', null, ['label' => 'field.game.genres'])
            ->add('platforms', null, ['label' => 'field.game.platforms'])
            ->add('createdAt', null, ['label' => 'field.created_at'])
            ->add('updatedAt', null, ['label' => 'field.updated_at']);
    }
}
