<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'profile.password.form.current_password.label',
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'profile.password.error.current_required',
                    ),
                    new SecurityAssert\UserPassword(
                        message: 'profile.password.error.current_invalid',
                    ),
                ],
                'attr' => [
                    'placeholder' => 'profile.password.form.current_password.placeholder',
                    'autocomplete' => 'current-password',
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'profile.password.form.new_password.label',
                    'attr' => [
                        'placeholder' => 'profile.password.form.new_password.placeholder',
                        'autocomplete' => 'new-password',
                    ],
                ],
                'second_options' => [
                    'label' => 'profile.password.form.confirm_password.label',
                    'attr' => [
                        'placeholder' => 'profile.password.form.confirm_password.placeholder',
                        'autocomplete' => 'new-password',
                    ],
                ],
                'invalid_message' => 'profile.password.error.mismatch',
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'profile.password.error.new_required',
                    ),
                    new Assert\Length(
                        min: 8,
                        max: 4096,
                        minMessage: 'profile.password.error.new_too_short',
                    ),
                    new Assert\Regex(
                        pattern: '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]+$/',
                        message: 'profile.password.error.new_invalid_format',
                    ),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'profile.password.form.submit',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'change_password',
            'translation_domain' => 'User',
        ]);
    }
}
