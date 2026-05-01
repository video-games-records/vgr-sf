<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, string>>
 */
class UserParametersFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('home_dashboard', ChoiceType::class, [
                'label' => 'profile.parameters.form.home_dashboard.label',
                'translation_domain' => 'User',
                'choices' => [
                    'profile.parameters.form.home_dashboard.choices.community' => 'community',
                    'profile.parameters.form.home_dashboard.choices.player' => 'player',
                ],
                'choice_translation_domain' => 'User',
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('score_form_per_page', ChoiceType::class, [
                'label' => 'profile.parameters.form.score_form_per_page.label',
                'translation_domain' => 'User',
                'choices' => [
                    '10' => '10',
                    '20' => '20',
                    '50' => '50',
                    '100' => '100',
                ],
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'profile.parameters.form.submit',
                'translation_domain' => 'User',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
