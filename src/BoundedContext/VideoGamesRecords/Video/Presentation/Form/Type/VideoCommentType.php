<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Video\Presentation\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class VideoCommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'comment.form.content.label',
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'comment.validation.content_required'),
                    new Length(
                        min: 3,
                        max: 1000,
                        minMessage: 'comment.validation.content_min_length',
                        maxMessage: 'comment.validation.content_max_length'
                    ),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'VgrVideo',
        ]);
    }
}
