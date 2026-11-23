<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Presentation\Form;

use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class TeamFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Declared (rather than raw <input type="hidden"> in the template) so the current
            // sort/order carried alongside a filter submission don't make Symfony treat them
            // as unrecognized extra fields (which marks the form invalid -> auto 422 response).
            ->add('sort', HiddenType::class, ['required' => false])
            ->add('order', HiddenType::class, ['required' => false])
            ->add('q', TextType::class, [
                'label' => 'team.index.filter.search',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'team.index.filter.search_placeholder'],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'team.index.filter.status',
                'required' => false,
                'placeholder' => 'team.index.filter.all_statuses',
                'choices' => [
                    'team.index.filter.status_open' => Team::STATUS_OPENED,
                    'team.index.filter.status_closed' => Team::STATUS_CLOSED,
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('minPlayers', IntegerType::class, [
                'label' => 'team.index.filter.min_players',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 0],
            ])
            ->add('pointsMin', IntegerType::class, [
                'label' => 'team.index.filter.points_min',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 0],
            ])
            ->add('pointsMax', IntegerType::class, [
                'label' => 'team.index.filter.points_max',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 0],
            ])
            ->add('hasBadges', CheckboxType::class, [
                'label' => 'team.index.filter.has_badges',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            'translation_domain' => 'VgrTeam',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
