<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Form;

use App\BoundedContext\User\Domain\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'registration.form.email.label',
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'registration.error.email_required',
                    ),
                    new Assert\Email(
                        message: 'registration.error.email_invalid',
                    ),
                ],
                'attr' => [
                    'placeholder' => 'registration.form.email.placeholder',
                    'autocomplete' => 'email',
                ],
            ])
            ->add('username', TextType::class, [
                'label' => 'registration.form.username.label',
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'registration.error.username_required',
                    ),
                    new Assert\Length(
                        min: 3,
                        max: 100,
                        minMessage: 'registration.error.username_too_short',
                        maxMessage: 'registration.error.username_too_long',
                    ),
                    new Assert\Regex(
                        pattern: '/^[a-zA-Z0-9_-]+$/',
                        message: 'registration.error.username_invalid_format',
                    ),
                ],
                'attr' => [
                    'placeholder' => 'registration.form.username.placeholder',
                    'autocomplete' => 'username',
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'registration.form.password.label',
                    'attr' => [
                        'placeholder' => 'registration.form.password.placeholder',
                        'autocomplete' => 'new-password',
                    ],
                ],
                'second_options' => [
                    'label' => 'registration.form.confirm_password.label',
                    'attr' => [
                        'placeholder' => 'registration.form.confirm_password.placeholder',
                        'autocomplete' => 'new-password',
                    ],
                ],
                'invalid_message' => 'registration.error.password_mismatch',
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'registration.error.password_required',
                    ),
                    new Assert\Length(
                        min: 8,
                        max: 4096,
                        minMessage: 'registration.error.password_too_short',
                    ),
                    new Assert\Regex(
                        pattern: '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]+$/',
                        message: 'registration.error.password_invalid_format',
                    ),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'registration.form.submit',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'register',
            'translation_domain' => 'User',
        ]);
    }
}
