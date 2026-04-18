<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

final class GameDownloadUrlAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'vgrcorebundle_admin_game_download_url';
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::PAGE] = 1;
        $sortValues[DatagridInterface::SORT_ORDER] = 'ASC';
        $sortValues[DatagridInterface::SORT_BY] = 'platform';
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('game')
            ->add('platform')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('game', null, [
                'label' => 'Jeu'
            ])
            ->add('platform', null, [
                'label' => 'Plateforme'
            ])
            ->add('url', null, [
                'label' => 'URL',
                'template' => '@VgrCore/Admin/Game/url_link.html.twig'
            ])
            ->add('createdAt', null, [
                'label' => 'Créé le'
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'label' => 'Actions',
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        // Ne pas afficher le champ 'game' quand on est en mode inline (contexte d'édition d'un jeu)
        if (!$this->hasParentFieldDescription()) {
            $form->add('game', null, [
                'label' => 'Jeu',
                'required' => true,
            ]);
        }

        $form
            ->add('platform', null, [
                'label' => 'Plateforme',
                'required' => true,
            ])
            ->add('url', UrlType::class, [
                'label' => 'URL de téléchargement',
                'required' => true,
                'help' => 'URL complète vers la page de téléchargement du jeu'
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('game', null, [
                'label' => 'Jeu'
            ])
            ->add('platform', null, [
                'label' => 'Plateforme'
            ])
            ->add('url', null, [
                'label' => 'URL'
            ])
            ->add('createdAt', null, [
                'label' => 'Créé le'
            ])
            ->add('updatedAt', null, [
                'label' => 'Modifié le'
            ])
        ;
    }
}
