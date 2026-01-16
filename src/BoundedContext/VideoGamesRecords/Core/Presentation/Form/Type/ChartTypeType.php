<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\ChartType;

class ChartTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'type',
            EntityType::class,
            [
                    'class' => ChartType::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('ct')
                            ->orderBy('ct.name', 'ASC');
                    },
                    'choice_label' => 'name'
                ]
        )
            ->add('submit', SubmitType::class);
    }
}
