<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Video\Presentation\Form\Type;

use App\BoundedContext\VideoGamesRecords\Video\Domain\Entity\Video;
use App\BoundedContext\VideoGamesRecords\Video\Domain\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class VideoEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'label' => 'my_videos.form.title.label',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'my_videos.form.title.placeholder',
                ],
                'help' => 'my_videos.form.title.help',
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'name',
                'label' => 'my_videos.form.tags.label',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'by_reference' => false,
                'attr' => [
                    'class' => 'form-select',
                    'multiple' => true,
                    'data-controller' => 'tags-tomselect',
                    'data-tags-tomselect-placeholder-value' => 'Select tags...',
                    'data-tags-tomselect-max-items-value' => 'null',
                ],
                'help' => 'my_videos.form.tags.help',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Video::class,
            'translation_domain' => 'VgrVideo',
        ]);
    }
}
