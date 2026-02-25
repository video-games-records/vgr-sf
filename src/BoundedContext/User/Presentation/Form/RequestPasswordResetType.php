<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<null>
 */
class RequestPasswordResetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'form.email',
                'translation_domain' => 'User',
                'constraints' => [
                    new NotBlank(
                        message: 'form.error.email.required',
                    ),
                    new Email(
                        message: 'form.error.email.invalid',
                    ),
                ],
                'attr' => [
                    'placeholder' => 'form.email.placeholder',
                ],
            ]);
    }
}
