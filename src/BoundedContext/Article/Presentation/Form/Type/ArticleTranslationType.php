<?php

declare(strict_types=1);

namespace App\BoundedContext\Article\Presentation\Form\Type;

use App\BoundedContext\Article\Domain\Entity\ArticleTranslation;
use App\SharedKernel\Presentation\Form\Type\RichTextEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<ArticleTranslation>
 */
class ArticleTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'article.form.title',
            ])
            ->add('content', RichTextEditorType::class, [
                'label' => 'article.form.content',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ArticleTranslation::class,
        ]);
    }
}
