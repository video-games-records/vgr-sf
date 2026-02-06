<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class AddFriendType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('player', TextType::class, [
                'label' => 'friend.add.label',
                'constraints' => [new NotBlank()],
                'attr' => [
                    'data-controller' => 'tomselect',
                    'data-tomselect-url-value' => '/api/players/autocomplete',
                    'data-tomselect-placeholder-value' => $options['player_placeholder'],
                    'data-tomselect-max-items-value' => '1',
                    'placeholder' => $options['player_placeholder'],
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'player_placeholder' => 'Search players...',
            'translation_domain' => 'VgrCore',
        ]);
    }
}
