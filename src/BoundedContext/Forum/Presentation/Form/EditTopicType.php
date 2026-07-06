<?php

declare(strict_types=1);

namespace App\BoundedContext\Forum\Presentation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class EditTopicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'label' => 'topic.edit.form.name',
            'required' => true,
            'constraints' => [
                new NotBlank(message: 'topic.edit.validation.name_required'),
                new Length(
                    min: 3,
                    max: 255,
                    minMessage: 'topic.edit.validation.name_too_short',
                    maxMessage: 'topic.edit.validation.name_too_long'
                ),
            ],
            'attr' => [
                'class' => 'form-control',
                'maxlength' => 255,
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
