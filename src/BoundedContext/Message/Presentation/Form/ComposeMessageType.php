<?php

declare(strict_types=1);

namespace App\BoundedContext\Message\Presentation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class ComposeMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('recipient', TextType::class, [
                'label' => 'compose.form.recipient',
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'compose.validation.recipient_required'),
                ],
                'attr' => [
                    'data-controller' => 'tomselect',
                    'data-tomselect-url-value' => $options['users_autocomplete_url'],
                    'data-tomselect-placeholder-value' => $options['recipient_placeholder'],
                    'data-tomselect-max-items-value' => '1',
                    'placeholder' => $options['recipient_placeholder'],
                ],
            ])
            ->add('object', TextType::class, [
                'label' => 'compose.form.subject',
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'compose.validation.subject_required'),
                    new Length(max: 255, maxMessage: 'compose.validation.subject_too_long'),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 255,
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'compose.form.message',
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'compose.validation.message_required'),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 10,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'users_autocomplete_url' => '/api/users/autocomplete',
            'recipient_placeholder' => 'Search user...',
            'translation_domain' => 'Message',
        ]);
    }
}
