<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Presentation\Form;

use App\BoundedContext\Forum\Domain\Entity\TopicType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
class CreateTopicFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'topic.create.form.name',
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'topic.create.validation.name_required'),
                    new Length(
                        min: 3,
                        max: 255,
                        minMessage: 'topic.create.validation.name_too_short',
                        maxMessage: 'topic.create.validation.name_too_long'
                    ),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 255,
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'topic.create.form.message',
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'topic.create.validation.message_required'),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 10,
                    'data-controller' => 'quill',
                ],
            ]);

        if ($options['is_admin']) {
            $builder->add('type', EntityType::class, [
                'label' => 'topic.create.form.type',
                'class' => TopicType::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'topic.create.form.type_placeholder',
                'attr' => [
                    'class' => 'form-select',
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'Forum',
            'is_admin' => false,
        ]);

        $resolver->setAllowedTypes('is_admin', 'bool');
    }
}
