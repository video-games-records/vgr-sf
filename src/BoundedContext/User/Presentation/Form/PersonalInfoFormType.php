<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Presentation\Form;

use App\BoundedContext\User\Domain\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class PersonalInfoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'profile.personal_info.form.username.label',
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'profile.personal_info.form.error.username_required',
                    ),
                    new Assert\Length(
                        min: 3,
                        max: 100,
                        minMessage: 'profile.personal_info.form.error.username_too_short',
                        maxMessage: 'profile.personal_info.form.error.username_too_long',
                    ),
                    new Assert\Regex(
                        pattern: '/^[a-zA-Z0-9_-]+$/',
                        message: 'profile.personal_info.form.error.username_invalid_format',
                    ),
                ],
                'attr' => [
                    'placeholder' => 'profile.personal_info.form.username.placeholder',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'profile.personal_info.form.email.label',
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'profile.personal_info.form.error.email_required',
                    ),
                    new Assert\Email(
                        message: 'profile.personal_info.form.error.email_invalid',
                    ),
                ],
                'attr' => [
                    'placeholder' => 'profile.personal_info.form.email.placeholder',
                ],
            ])
            ->add('language', ChoiceType::class, [
                'label' => 'profile.personal_info.form.language.label',
                'choices' => [
                    '🇬🇧 English' => 'en',
                    '🇫🇷 Français' => 'fr',
                    '🇩🇪 Deutsch' => 'de',
                    '🇮🇹 Italiano' => 'it',
                    '🇯🇵 日本語' => 'ja',
                    '🇪🇸 Español' => 'es',
                    '🇧🇷 Português (Brasil)' => 'pt_BR',
                    '🇨🇳 中文 (简体)' => 'zh_CN',
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'profile.personal_info.form.submit',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'personal_info',
            'translation_domain' => 'User',
        ]);
    }
}
