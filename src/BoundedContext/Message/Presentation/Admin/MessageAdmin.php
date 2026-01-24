<?php

declare(strict_types=1);

namespace App\BoundedContext\Message\Presentation\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use App\SharedKernel\Presentation\Form\Type\RichTextEditorType;

class MessageAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'message_admin';

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('export')
            ->remove('create');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('id', TextType::class, ['label' => 'label.id', 'attr' => ['readonly' => true]])
            ->add('sender', ModelListType::class, [
                'class' => 'App\BoundedContext\User\Domain\Entity\User',
                'btn_add' => false,
                'btn_list' => false,
                'btn_delete' => false,
                'btn_catalogue' => false,
                'label' => 'label.sender',
                'required' => false
            ])
            ->add('recipient', ModelListType::class, [
                'class' => 'App\BoundedContext\User\Domain\Entity\User',
                'btn_add' => false,
                'btn_list' => false,
                'btn_delete' => false,
                'btn_catalogue' => false,
                'label' => 'label.recipient',
            ])
            ->add('type', TextType::class, ['label' => 'label.type'])
            ->add('object', TextType::class, ['label' => 'label.object'])
            ->add('message', RichTextEditorType::class, [
                'label' => 'label.message',
                'required' => true,
            ])
            ->add('isOpened', null, ['label' => 'label.isOpened', 'required' => false])
            ->add('isDeletedSender', null, ['label' => 'label.isDeletedSender', 'required' => false])
            ->add('isDeletedRecipient', null, ['label' => 'label.isDeletedRecipient', 'required' => false]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('sender', ModelFilter::class, [
                'label' => 'label.sender',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'property' => 'username',
                    'class' => 'App\BoundedContext\User\Domain\Entity\User'
                ],
            ])
            ->add('recipient', ModelFilter::class, [
                'label' => 'label.recipient',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'property' => 'username',
                    'class' => 'App\BoundedContext\User\Domain\Entity\User'
                ],
            ])
            ->add('object', null, ['label' => 'label.object'])
            ->add('isOpened', null, ['label' => 'label.isOpened'])
            ->add('isDeletedSender', null, ['label' => 'label.isDeletedSender'])
            ->add('isDeletedRecipient', null, ['label' => 'label.isDeletedRecipient'])
            ->add('type', null, ['label' => 'label.type'])
            ->add('createdAt', null, ['label' => 'label.createdAt']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id', null, ['label' => 'label.id'])
            ->add('type', null, ['label' => 'label.type'])
            ->add('object', null, ['label' => 'label.object'])
            ->add('sender', null, ['label' => 'label.sender'])
            ->add('recipient', null, ['label' => 'label.recipient'])
            ->add('isOpened', null, ['label' => 'label.isOpened'])
            ->add('isDeletedSender', null, ['label' => 'label.isDeletedSender'])
            ->add('isDeletedRecipient', null, ['label' => 'label.isDeletedRecipient'])
            ->add('createdAt', null, ['label' => 'label.createdAt'])
            ->add('_action', 'actions', ['actions' => ['show' => [], 'edit' => []]]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('id', null, ['label' => 'label.id'])
            ->add('type', null, ['label' => 'label.type'])
            ->add('object', null, ['label' => 'label.object'])
            ->add('sender', null, ['label' => 'label.sender'])
            ->add('recipient', null, ['label' => 'label.recipient'])
            ->add('isOpened', null, ['label' => 'label.isOpened'])
            ->add('isDeletedSender', null, ['label' => 'label.isDeletedSender'])
            ->add('isDeletedRecipient', null, ['label' => 'label.isDeletedRecipient'])
            ->add('createdAt', null, ['label' => 'label.createdAt'])
            ->add('updatedAt', null, ['label' => 'label.updatedAt'])
            ->add('message', null, ['label' => 'label.message', 'safe' => true]);
    }
}
