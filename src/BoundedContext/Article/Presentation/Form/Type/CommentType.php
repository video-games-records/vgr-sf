<?php

declare(strict_types=1);

namespace App\BoundedContext\Article\Presentation\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'comment.form.content',
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'comment.validation.content_required'),
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
            'translation_domain' => 'Article',
        ]);
    }
}
