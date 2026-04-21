<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Presentation\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use App\SharedKernel\Presentation\Form\Type\RichTextEditorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MessageAdmin extends AbstractAdmin
{
    protected int $maxPerPage = 50;

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'pnf_admin_message';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('export');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('id', TextType::class, ['label' => 'label.id', 'attr' => ['readonly' => true]])
            ->add('message', RichTextEditorType::class, ['label' => 'label.message'])
            ->add('topic')
            ->add('position', TextType::class, ['label' => 'label.position']);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('topic', null, ['label' => 'label.topic'])
            ->add('user', null, ['label' => 'label.user']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id', null, ['label' => 'label.id'])
            ->add('topic', null, ['label' => 'label.topic'])
            ->add('user', null, ['label' => 'label.user'])
            ->add('position', null, ['label' => 'label.position'])
            ->add('createdAt', null, ['label' => 'label.createdAt'])
            ->add('_action', 'actions', ['actions' => ['show' => [], 'edit' => []]]);
    }

    public function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query = parent::configureQuery($query);
        $query->leftJoin($query->getRootAliases()[0] . '.topic', 't')
              ->leftJoin($query->getRootAliases()[0] . '.user', 'u')
              ->addSelect('t', 'u');
        return $query;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues['_sort_by'] = 'id';
        $sortValues['_sort_order'] = 'DESC';
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('id', null, ['label' => 'label.id'])
            ->add('message', null, ['label' => 'label.message'])
            ->add('topic', null, ['label' => 'label.topic'])
            ->add('user', null, ['label' => 'label.user'])
            ->add('position', null, ['label' => 'label.position'])
            ->add('createdAt', null, ['label' => 'label.createdAt']);
    }
}
