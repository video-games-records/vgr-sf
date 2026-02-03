<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Form;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Country;
use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Player;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class PlayerProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('website', UrlType::class, [
                'label' => 'profile.player.form.website.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'profile.player.form.website.placeholder',
                ],
            ])
            ->add('youtube', TextType::class, [
                'label' => 'profile.player.form.youtube.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'profile.player.form.youtube.placeholder',
                ],
            ])
            ->add('twitch', TextType::class, [
                'label' => 'profile.player.form.twitch.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'profile.player.form.twitch.placeholder',
                ],
            ])
            ->add('discord', TextType::class, [
                'label' => 'profile.player.form.discord.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'profile.player.form.discord.placeholder',
                ],
            ])
            ->add('presentation', TextareaType::class, [
                'label' => 'profile.player.form.presentation.label',
                'required' => false,
                'attr' => [
                    'data-controller' => 'quill',
                    'data-quill-toolbar-value' => 'minimal',
                    'data-quill-min-height-value' => '150px',
                ],
            ])
            ->add('collection', TextareaType::class, [
                'label' => 'profile.player.form.collection.label',
                'required' => false,
                'attr' => [
                    'data-controller' => 'quill',
                    'data-quill-toolbar-value' => 'minimal',
                    'data-quill-min-height-value' => '150px',
                ],
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'profile.player.form.birth_date.label',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('country', EntityType::class, [
                'label' => 'profile.player.form.country.label',
                'required' => false,
                'class' => Country::class,
                'choice_label' => fn (Country $country): ?string => $country->getName(),
                'placeholder' => 'profile.player.form.country.placeholder',
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'profile.player.form.submit',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Player::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'player_profile',
            'translation_domain' => 'VgrCore',
        ]);
    }
}
