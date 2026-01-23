<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Form;

use App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject\PlayerChartStatusEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class SearchPlayerChartForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('games', TextType::class, [
                'label' => 'Games',
                'required' => false,
                'attr' => [
                    'data-controller' => 'tomselect',
                    'data-tomselect-url-value' => $options['games_autocomplete_url'],
                    'data-tomselect-locale-value' => $options['locale'],
                    'data-tomselect-placeholder-value' => $options['games_placeholder'],
                    'placeholder' => $options['games_placeholder'],
                ],
            ])
            ->add('players', TextType::class, [
                'label' => 'Players',
                'required' => false,
                'attr' => [
                    'data-controller' => 'tomselect',
                    'data-tomselect-url-value' => $options['players_autocomplete_url'],
                    'data-tomselect-placeholder-value' => $options['players_placeholder'],
                    'placeholder' => $options['players_placeholder'],
                ],
            ])
            ->add('platforms', TextType::class, [
                'label' => 'Platforms',
                'required' => false,
                'attr' => [
                    'data-controller' => 'tomselect',
                    'data-tomselect-url-value' => $options['platforms_autocomplete_url'],
                    'data-tomselect-placeholder-value' => $options['platforms_placeholder'],
                    'placeholder' => $options['platforms_placeholder'],
                ],
            ])
            ->add('statuses', ChoiceType::class, [
                'label' => 'Status',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'choices' => array_combine(
                    array_map(fn(PlayerChartStatusEnum $status) => $status->getLabel(), PlayerChartStatusEnum::cases()),
                    PlayerChartStatusEnum::cases()
                ),
                'choice_value' => fn(?PlayerChartStatusEnum $status) => $status?->value,
                'attr' => [
                    'class' => 'form-select',
                    'size' => 4,
                ],
            ])
            ->add('rank_operator', ChoiceType::class, [
                'label' => 'Rank',
                'required' => false,
                'choices' => [
                    '≤' => 'lte',
                    '<' => 'lt',
                    '=' => 'eq',
                    '>' => 'gt',
                    '≥' => 'gte',
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('rank_value', IntegerType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter a rank...',
                    'min' => 1,
                ],
            ])
            ->add('points_operator', ChoiceType::class, [
                'label' => 'Points',
                'required' => false,
                'choices' => [
                    '≤' => 'lte',
                    '<' => 'lt',
                    '=' => 'eq',
                    '>' => 'gt',
                    '≥' => 'gte',
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('points_value', IntegerType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter points...',
                    'min' => 0,
                ],
            ])
            ->add('platinum_only', CheckboxType::class, [
                'label' => 'Platinum records only',
                'required' => false,
                'help' => 'Show only 1st place records without ties',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Search',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'games_autocomplete_url' => '/api/games/autocomplete',
            'players_autocomplete_url' => '/api/players/autocomplete',
            'platforms_autocomplete_url' => '/api/platforms/autocomplete',
            'locale' => 'en',
            'games_placeholder' => 'Search games...',
            'players_placeholder' => 'Search players...',
            'platforms_placeholder' => 'Search platforms...',
        ]);
    }
}
