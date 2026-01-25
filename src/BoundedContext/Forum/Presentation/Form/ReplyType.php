<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Presentation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class ReplyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextareaType::class, [
                'label' => 'topic.reply.form.message',
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'topic.reply.validation.message_required'),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 10,
                    'data-controller' => 'quill',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'Forum',
        ]);
    }
}
