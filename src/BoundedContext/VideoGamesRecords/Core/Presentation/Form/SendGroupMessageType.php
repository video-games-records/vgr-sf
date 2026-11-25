<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class SendGroupMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => 'game.send_message.form.subject',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 255),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'game.send_message.form.message',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'attr' => [
                    'rows' => 10,
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'game.send_message.form.submit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'VgrCoreAdmin',
            // Not in framework.csrf_protection.stateless_token_ids on purpose: the Sonata Admin
            // layout doesn't load the app's Stimulus assets, so the double-submit cookie used by
            // the default "submit" token_id is never set there, which fails CSRF validation as
            // soon as the session has proven double-submit elsewhere (e.g. the front-end login).
            'csrf_token_id' => 'admin_send_group_message',
        ]);
    }
}
