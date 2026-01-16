<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Presentation\Admin;

use App\BoundedContext\Forum\Domain\ValueObject\ForumStatus;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\BoundedContext\Forum\Domain\Entity\Forum;

class ForumAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'pnf_admin_forum';

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('export');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('id', TextType::class, ['label' => 'label.id', 'attr' => ['readonly' => true]])
            ->add('libForum', TextType::class, ['label' => 'label.forum'])
            ->add('libForumFr', TextType::class, ['label' => 'label.forumFr'])
            ->add('category')
            ->add(
                'status',
                ChoiceType::class,
                [
                    'label' => 'label.status',
                    'choices' => ForumStatus::getStatusChoices(),
                ]
            )
            ->add('position', TextType::class, ['label' => 'label.position', 'required' => true]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id', null, ['label' => 'label.id'])
            ->add('category', null, ['label' => 'label.category'])
            ->add('libForum', null, ['label' => 'label.forum'])
            ->add('status', null, ['label' => 'label.status']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id', null, ['label' => 'label.id'])
            ->add('category', null, ['label' => 'label.category'])
            ->add('libForum', null, ['label' => 'label.forum'])
            ->add('status', null, ['label' => 'label.status'])
            ->add('position', null, ['label' => 'label.position'])
            ->add('_action', 'actions', ['actions' => ['show' => [], 'edit' => []]]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('id', null, ['label' => 'label.id'])
            ->add('category', null, ['label' => 'label.category'])
            ->add('libForum', null, ['label' => 'label.forum'])
            ->add('libForumFr', null, ['label' => 'label.forumFr'])
            ->add('position', null, ['label' => 'label.position'])
            ->add('nbTopic', null, ['label' => 'label.nbTopic'])
            ->add('nbMessage', null, ['label' => 'label.nbMessage'])
            ->add('lastMessage', null, ['label' => 'label.lastMessage']);
    }
}
