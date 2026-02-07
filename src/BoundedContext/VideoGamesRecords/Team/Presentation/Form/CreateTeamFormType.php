<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Team\Presentation\Form;

use App\BoundedContext\VideoGamesRecords\Team\Domain\Entity\Team;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class CreateTeamFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libTeam', TextType::class, [
                'label' => 'team.manage.form.name.label',
                'attr' => [
                    'placeholder' => 'team.manage.form.name.placeholder',
                ],
            ])
            ->add('tag', TextType::class, [
                'label' => 'team.manage.form.tag.label',
                'attr' => [
                    'placeholder' => 'team.manage.form.tag.placeholder',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'team.manage.form.create',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'create_team',
            'translation_domain' => 'VgrTeam',
        ]);
    }
}
