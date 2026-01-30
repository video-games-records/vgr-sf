<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Form;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Game;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class LostPositionFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $options['locale'];
        $games = $options['games'];

        $choices = [];
        /** @var Game $game */
        foreach ($games as $game) {
            $choices[$game->getName($locale)] = $game->getId();
        }

        $builder
            ->add('game', ChoiceType::class, [
                'label' => 'lost_position.filter.game',
                'required' => false,
                'placeholder' => 'lost_position.filter.all_games',
                'choices' => $choices,
                'attr' => [
                    'class' => 'form-select',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'games' => [],
            'locale' => 'en',
            'method' => 'GET',
            'csrf_protection' => false,
            'translation_domain' => 'VgrCore',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
