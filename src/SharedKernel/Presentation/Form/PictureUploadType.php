<?php

declare(strict_types=1);

namespace App\SharedKernel\Presentation\Form;

use App\SharedKernel\Infrastructure\FileSystem\Manager\PictureUploadManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class PictureUploadType extends AbstractType
{
    public function __construct(private readonly PictureUploadManager $pictureUploadManager)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('picture', FileType::class, [
                'label' => 'picture_upload.form.picture.label',
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'picture_upload.form.error.file_required',
                    ),
                    new Assert\File(
                        maxSize: '5M',
                        mimeTypes: $this->pictureUploadManager->getAllowedMimeTypes(),
                        mimeTypesMessage: 'picture_upload.form.error.mime_type',
                        maxSizeMessage: 'picture_upload.form.error.max_size',
                    ),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'picture_upload.form.submit',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'picture_upload',
            'translation_domain' => 'SharedKernel',
        ]);
    }
}
