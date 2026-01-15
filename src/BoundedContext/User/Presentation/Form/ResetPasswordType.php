<?php

namespace App\BoundedContext\User\Presentation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<null>
 */
class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'form.password',
                    'translation_domain' => 'User',
                    'attr' => [
                        'placeholder' => 'form.password.placeholder',
                    ],
                ],
                'second_options' => [
                    'label' => 'form.password.confirm',
                    'translation_domain' => 'User',
                    'attr' => [
                        'placeholder' => 'form.password.confirm.placeholder',
                    ],
                ],
                'invalid_message' => 'form.error.password.mismatch',
                'constraints' => [
                    new NotBlank(
                        message: 'form.error.password.required',
                    ),
                    new Length(
                        min: 6,
                        minMessage: 'form.error.password.min_length',
                        max: 4096,
                    ),
                ],
            ]);
    }
}
