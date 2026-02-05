<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class AvatarUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('avatar', FileType::class, [
                'label' => 'profile.picture.form.avatar.label',
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'profile.picture.form.error.file_required',
                    ),
                    new Assert\File(
                        maxSize: '2M',
                        mimeTypes: ['image/png', 'image/jpeg'],
                        mimeTypesMessage: 'profile.picture.form.error.mime_type',
                        maxSizeMessage: 'profile.picture.form.error.max_size',
                    ),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'profile.picture.form.submit',
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
            'csrf_token_id' => 'avatar_upload',
            'translation_domain' => 'User',
        ]);
    }
}
