<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Presentation\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TopicAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'pnf_admin_topic';

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('export');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('id', TextType::class, ['label' => 'label.id', 'attr' => ['readonly' => true]])
            ->add('name', TextType::class, ['label' => 'label.name'])
            ->add('forum')
            ->add('type');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name', null, ['label' => 'label.name'])
            ->add('forum', null, ['label' => 'label.forum'])
            ->add('type', null, ['label' => 'label.type']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id', null, ['label' => 'label.id'])
            ->add('name', null, ['label' => 'label.name'])
            ->add('forum', null, ['label' => 'label.forum'])
            ->add('type', null, ['label' => 'label.type'])
            ->add('nbMessage', null, ['label' => 'label.nbMessage'])
            ->add('_action', 'actions', ['actions' => ['show' => [], 'edit' => []]]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('id', null, ['label' => 'label.id'])
            ->add('name', null, ['label' => 'label.name'])
            ->add('forum', null, ['label' => 'label.forum'])
            ->add('type', null, ['label' => 'label.type'])
            ->add('nbMessage', null, ['label' => 'label.nbMessage']);
    }
}
